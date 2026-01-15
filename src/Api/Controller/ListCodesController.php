<?php

namespace HertzDev\RedemptionCode\Api\Controller;

use Flarum\Http\RequestUtil;
use Flarum\Api\Controller\AbstractListController;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use Illuminate\Database\ConnectionInterface;
use HertzDev\RedemptionCode\Api\Serializer\RedemptionCodeSerializer;

class ListCodesController extends AbstractListController
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

        // 获取所有兑换码，按创建时间倒序
        return $this->db->table('redemption_codes')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}