<?php

namespace App\Components\Integrations\DriveMedia;

abstract class StatusCode
{
    const OK = 1;
    const USER_NOT_FOUND = 2;
    const INVALID_SIGNATURE = 3;
    const INTERNAL_SERVER_ERROR = 4;
}