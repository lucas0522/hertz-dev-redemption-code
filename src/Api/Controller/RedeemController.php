<?php

namespace HertzDev\RedemptionCode\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Illuminate\Database\ConnectionInterface;
use Flarum\Foundation\ValidationException;
use Carbon\Carbon;

class RedeemController implements RequestHandlerInterface
{
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertRegistered(); // 必须登录

        $body = $request->getParsedBody();
        $codeStr = Arr::get($body, 'code');

        if (!$codeStr) {
            throw new ValidationException(['code' => '请输入兑换码']);
        }

        // 1. 验证兑换码
        $codeRecord = $this->db->table('redemption_codes')
            ->where('code', $codeStr)
            ->where('is_used', false) // 必须未被使用
            ->first();

        if (!$codeRecord) {
            throw new ValidationException(['code' => '兑换码无效或已被使用']);
        }

        // 2. 解析 payload
        $payload = json_decode($codeRecord->payload, true);
        
        // 3. 执行联动逻辑：群组时间充值
        if ($codeRecord->type === 'group_time') {
            $groupId = $payload['groupId'];
            $daysToAdd = (int)$payload['days'];
            
            // === 这里复用了你之前的“叠加”核心算法 ===
            $this->applyGroupTime($actor, $groupId, $daysToAdd);
        }

        // 4. 标记为已使用 (核销)
        $this->db->table('redemption_codes')
            ->where('id', $codeRecord->id)
            ->update([
                'is_used' => true,
                'used_by' => $actor->id,
                'used_at' => Carbon::now()
            ]);

        return new EmptyResponse();
    }

    // 封装的充值逻辑
    protected function applyGroupTime($user, $groupId, $days)
    {
        $now = Carbon::now();

        // A. 查另一个扩展的表 (group_expiration)
        $existingRecord = $this->db->table('group_expiration')
            ->where('user_id', $user->id)
            ->where('group_id', $groupId)
            ->first();

        // B. 确定基准时间
        $existingExpiration = $existingRecord ? Carbon::parse($existingRecord->expiration_date) : null;
        
        if ($existingExpiration && $existingExpiration->isFuture()) {
            $baseDate = $existingExpiration;
        } else {
            $baseDate = $now;
        }

        // C. 叠加计算
        $finalDate = $baseDate->copy()->addDays($days);

        // D. 写入
        $this->db->table('group_expiration')->updateOrInsert(
            ['user_id' => $user->id, 'group_id' => $groupId],
            [
                'expiration_date' => $finalDate,
                'created_at' => $now,
                'updated_at' => $now
            ]
        );

        // E. 确保用户在群组里
        $userModel = User::find($user->id);
        $userModel->groups()->syncWithoutDetaching([$groupId]);
    }
}