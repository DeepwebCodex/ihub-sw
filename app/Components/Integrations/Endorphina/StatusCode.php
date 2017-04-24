<?php

namespace App\Components\Integrations\Endorphina;

use iHubGrid\ErrorHandler\Http\CodeMappingBase;



abstract class StatusCode
{

    const SERVER_ERROR = 1;
    const UNKNOWN_METHOD = 2;
    const INVALID_TOKEN = 3;
    const INVALID_AUTH = 4;
    const SIGNATURE_WRONG = 5;
    const BAD_ORDER = 6;
    const INSUFFICIENT_FUNDS = 7;
    //external code error
    const EXTERNAl_INTERNAL_ERROR = 'INTERNAL_ERROR';
    const EXTERNAl_TOKEN_NOT_FOUND = 'TOKEN_NOT_FOUND';
    const EXTERNAl_TOKEN_EXPIRED = 'TOKEN_EXPIRED';
    const EXTERNAl_INSUFFICIENT_FUNDS = 'INSUFFICIENT_FUNDS';
    const EXTERNAl_ACCESS_DENIED = 'ACCESS_DENIED';
    const EXTERNAl_CODE_ERROR = [
        1 => self::EXTERNAl_INTERNAL_ERROR,
        2 => self::EXTERNAl_INTERNAL_ERROR,
        3 => self::EXTERNAl_TOKEN_NOT_FOUND,
        4 => self::EXTERNAl_TOKEN_NOT_FOUND,
        5 => self::EXTERNAl_ACCESS_DENIED,
        CodeMappingBase::INVALID_TOKEN => self::EXTERNAl_TOKEN_NOT_FOUND,
        CodeMappingBase::INVALID_CURRENCY => self::EXTERNAl_INTERNAL_ERROR,
        6 => self::EXTERNAl_INTERNAL_ERROR,
        7 => self::EXTERNAl_INSUFFICIENT_FUNDS
    ];

}
