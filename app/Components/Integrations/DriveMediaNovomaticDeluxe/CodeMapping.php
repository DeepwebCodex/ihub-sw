<?php

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;

class CodeMapping extends CodeMappingBase {
    

    public static function getMapping() {
        return [
            StatusCode::UNKNOWN_METHOD => [
                'message' => 'Unknown method',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::UNKNOWN_METHOD],
                'default' => true
            ], StatusCode::INVALID_TOKEN => [
                'message' => 'user_not_found',
                'map' => [],
                'attribute' => 'token',
                'meanings' => [self::INVALID_TOKEN]
            ], StatusCode::INVALID_AUTH => [
                'message' => 'user_not_found',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::INVALID_TOKEN]
            ], StatusCode::INVALID_SIGNATURE => [
                'message' => 'error_sign',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::INVALID_SIGNATURE]
            ], StatusCode::DUPLICATE => [
                'message' => 'repeat_operation',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::DUPLICATE]
            ], StatusCode::USER_NOT_FOUND => [
                'message' => 'user_not_found',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::USER_NOT_FOUND]
            ], StatusCode::SERVER_ERROR => [
                'message' => 'internal_error',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::SERVER_ERROR]
            ], StatusCode::BAD_CODITION => [
                'message' => 'Bad condition for finance operation',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::SERVER_ERROR]
            ], StatusCode::FAIL_BALANCE => [
                'message' => 'fail_balance',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::NO_MONEY]
            ],StatusCode::BAD_ORDER => [
                'message' => 'internal_error',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::SERVER_ERROR]
            ]
        ];
    }

}
