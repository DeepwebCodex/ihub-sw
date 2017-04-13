<?php

namespace App\Components\Integrations\GameArt;

use App\Components\Integrations\GameArt\StatusCode;
use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    const INVALID_KEY = 'Invalid key';

    public static function getMapping()
    {
        return [
            StatusCode::OK => [
                'message'   => 'OK',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SUCCESS]
            ],
            StatusCode::SERVER_ERROR => [
                'message'   => 'Server error',
                'map'       => [],
                'attribute' => null,
                'default'   => true,
                'meanings'  => [self::SERVER_ERROR, self::USER_NOT_FOUND, self::DUPLICATE, self::UNKNOWN_METHOD, self::INVALID_KEY]
            ],
            StatusCode::INSUFFICIENT_FUNDS => [
                'message'   => 'Insufficient funds',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
        ];
    }

}