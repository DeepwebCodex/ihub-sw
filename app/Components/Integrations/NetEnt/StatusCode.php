<?php

namespace App\Components\Integrations\NetEnt;

abstract class StatusCode
{
    /** not implemented in NetEnt API */
    const OK = 0;
    const HMAC = 1;
    const CURRENCY = 2;
    const TOKEN = 3;
    const INSUFFICIENT_FUNDS = 4;
    const BAD_OPERATION_ORDER = 5;
    const UNKNOWN = 6;
    const DUPLICATED_WIN = 7;
    const METHOD = 8;
    const VALIDATION = 9;
    const TRANSACTION_MISMATCH = 10;
    const DUPLICATED_TRANSACTION = 11;
}