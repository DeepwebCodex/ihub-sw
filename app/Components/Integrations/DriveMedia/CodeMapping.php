<?php

namespace App\Components\Integrations\DriveMedia;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;

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
                'message'   => 'user_not_found',
                'map'       => [1024, 1410],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            StatusCode::INVALID_SIGNATURE => [
                'message'   => 'error_sign',
                'map'       => [],
                'attribute'  => null,
                'meanings'   => [self::INVALID_SIGNATURE]
            ],
            StatusCode::INTERNAL_SERVER_ERROR => [
                'message'   => 'internal_error',
                'map'       => [],
                'attribute'  => null,
                'default'   => true,
                'meanings'   => [self::SERVER_ERROR]
            ],
        ];
    }
}