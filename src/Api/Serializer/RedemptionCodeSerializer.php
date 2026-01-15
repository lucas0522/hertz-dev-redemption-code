<?php

namespace HertzDev\RedemptionCode\Api\Serializer;

use Flarum\Api\Serializer\AbstractSerializer;

class RedemptionCodeSerializer extends AbstractSerializer
{
    protected $type = 'redemption-codes';

    protected function getDefaultAttributes($model)
    {
        return [
            'code'      => $model->code,
            'type'      => $model->type,
            'payload'   => $model->payload,
            'isUsed'    => (bool) $model->is_used,
            'usedAt'    => $this->formatDate($model->used_at),
            'createdAt' => $this->formatDate($model->created_at),
        ];
    }
}