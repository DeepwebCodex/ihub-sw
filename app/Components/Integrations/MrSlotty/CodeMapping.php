<?php

namespace App\Components\Integrations\MrSlotty;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    public static function getMapping()
    {
        return [
            StatusCode::OK => [
                'message'   => 'OK',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::USER_NOT_FOUND => [
                'message'   => 'Player authentication failed.',
                'map'       => [1024, 1410],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            StatusCode::INVALID_SIGNATURE => [
                'message'   => 'Unauthorized request.',
                'map'       => [],
                'attribute'  => null,
                'meanings'   => [self::INVALID_SIGNATURE]
            ],
            StatusCode::INTERNAL_SERVER_ERROR => [
                'message'   => 'Unknown error occurred.',
                'map'       => [],
                'attribute'  => null,
                'default'   => true,
                'meanings'   => [self::SERVER_ERROR]
            ],
            StatusCode::DUPLICATE => [
                'message' => 'Duplicate transaction request.',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::DUPLICATE]
            ],
            StatusCode::NO_MONEY => [
                'message' => 'Insufficient funds to place current wager. Please reduce the stake or add more funds to your balance.',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::NO_MONEY]
            ],
            StatusCode::INVALID_SUM => [
                'message' => 'This wagering will exceed your wagering limitation. Please try a smaller amount or increase the limit.',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::INVALID_SUM]
            ],
        ];
    }
}