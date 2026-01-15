<?php

use Flarum\Extend;
use HertzDev\RedemptionCode\Api\Controller;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    // 后台管理 API
    (new Extend\Routes('api'))
        ->get('/redemption-codes', 'redemption.list', Controller\ListCodesController::class)
        ->post('/redemption-codes', 'redemption.create', Controller\CreateCodeController::class)
        // 前台兑换 API
        ->post('/redemption/redeem', 'redemption.redeem', Controller\RedeemController::class),
];