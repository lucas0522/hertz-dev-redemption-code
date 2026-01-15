<?php

namespace HertzDev\RedemptionCode\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Api\Controller\AbstractCreateController;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use HertzDev\RedemptionCode\Api\Serializer\RedemptionCodeSerializer;

class CreateCodeController extends AbstractCreateController
{
    public $serializer = RedemptionCodeSerializer::class;
    protected $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    protected function data(ServerRequestInterface $request, Document $document)
    {
        RequestUtil::getActor($request)->assertAdmin();

        $data = Arr::get($request->getParsedBody(), 'data.attributes');
        $groupId = Arr::get($data, 'groupId');
        $days = Arr::get($data, 'days');
        $amount = Arr::get($data, 'amount', 1);

        $results = [];
        $now = Carbon::now();
        $payload = json_encode(['groupId' => $groupId, 'days' => $days]);

        for ($i = 0; $i < $amount; $i++) {
            $codeStr = 'VIP-' . strtoupper(Str::random(8));
            $id = $this->db->table('redemption_codes')->insertGetId([
                'code' => $codeStr,
                'type' => 'group_time',
                'payload' => $payload,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            // 为了返回给前端显示，我们需要查出来
            $results[] = $this->db->table('redemption_codes')->where('id', $id)->first();
        }

        // 这里稍微偷个懒，只返回最后一个生成的，或者你可以修改 logic 返回数组
        // 标准 JSON:API 一个请求通常返回一个资源，这里我们返回刚生成的最后一个供前端刷新列表即可
        return end($results);
    }
}