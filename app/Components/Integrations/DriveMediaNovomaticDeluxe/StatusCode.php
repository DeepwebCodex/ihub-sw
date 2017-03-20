<?php

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

abstract class StatusCode {

    const SERVER_ERROR = 1;
    const UNKNOWN_METHOD = 2;
    const INVALID_TOKEN = 3;
    const INVALID_AUTH = 4;
    const INVALID_SIGNATURE = 5;
    const DUPLICATE = 6;
    const USER_NOT_FOUND = 7;
    const BAD_CODITION = 8;
    const FAIL_BALANCE = 9;
    const BAD_ORDER = 10;

}
