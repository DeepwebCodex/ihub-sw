<?php

namespace App\Components\Integrations\DriveMediaNovomatic;

use App\Components\Integrations\CodeMappingBase;

/**
 * Class CodeMapping
 * @package App\Components\Integrations\Novomatic
 */
class CodeMapping extends CodeMappingBase
{
    const INVALID_AUTH = 'invalid_auth';

    /**
     * @return array
     */
    public static function getMapping()
    {
        return [
            StatusCode::OK => [
                'message' => 'OK',
                'meanings' => [self::SUCCESS]
            ],
            StatusCode::USER_NOT_FOUND => [
                'message' => 'user_not_found',
                'map' => [1024, 1410],
                'meanings' => [self::USER_NOT_FOUND]
            ],
            StatusCode::INVALID_SIGNATURE => [
                'message' => 'error_sign',
                'meanings' => [self::INVALID_SIGNATURE]
            ],
            StatusCode::INTERNAL_SERVER_ERROR => [
                'message' => 'internal_error',
                'default' => true,
                'meanings' => [self::SERVER_ERROR]
            ],
        ];
    }
}
