<?php

namespace App\Components\Integrations\Casino;

abstract class StatusCode
{
    const SERVER_ERROR = 0;
    const SUCCESS = 1;
    const NO_MONEY = 2;
    const CURRENCY_MISMATCH = 3;
    const USER_NOT_FOUND = 4;
    const INCORRECT_RESPONSE = 5;
    const INVALID_TOKEN = 6;
    const INCORRECT_USER_ID = 7;
    const INCORRECT_RESULT = 8;
    const UNKNOWN_IMPRINT = 9;
    const TIME_EXPIRED = 10;
    const INVALID_SIGNATURE = 11;
    const WRONG_AMOUNT = 12;
    const INVALID_SERVICE = 13;
    const INVALID_WALLET = 14;
}