<?php

namespace App\Components\Integrations\Endorphina;

use App\Components\Integrations\CodeMappingBase;
use App\Components\Integrations\Endorphina\StatusCode;

class CodeMapping extends CodeMappingBase
{

    public static function getMapping()
    {
        return [
            StatusCode::UNKNOWN_METHOD => [
                'message' => 'Unknown method',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::UNKNOWN_METHOD]
            ],
            StatusCode::SERVER_ERROR => [
                'message' => 'Unknown error',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::SERVER_ERROR],
                'default' => true
            ],
            StatusCode::SIGNATURE_WRONG => [
                'message' => 'Signature wrong',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::INVALID_SIGNATURE]
            ],
            self::INVALID_CURRENCY => [
                'message' => 'Invalid currency',
                'map' => [],
                'attribute' => null,
                'meanings' => [self::INVALID_CURRENCY]
            ],
            StatusCode::BAD_ORDER => [
                'message' => 'Bad order transactions',
                'map' => [],
                'attribute' => null,
                'meanings' => []
            ],
            StatusCode::INSUFFICIENT_FUNDS => [
                'message' => 'Insufficient funds',
                'map' => [],
                'attribute' => null,
                'meanings' => []
            ],
            
        ];
    }

    //var mixed $errorCode
    public static function getExternalErrorCode($errorCode)
    {
        if (isset(StatusCode::EXTERNAl_CODE_ERROR[$errorCode])) {
            return StatusCode::EXTERNAl_CODE_ERROR[$errorCode];
        } else {
            return StatusCode::EXTERNAl_INTERNAL_ERROR;
        }
    }

}
