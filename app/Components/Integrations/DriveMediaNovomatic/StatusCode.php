<?php

namespace App\Components\Integrations\DriveMediaNovomatic;

/**
 * Class StatusCode
 * @package App\Components\Integrations\Novomatic
 */
abstract class StatusCode
{
    const OK = 2000;
    const USER_NOT_FOUND = 4004;
    const INVALID_SIGNATURE = 4001;
    const INTERNAL_SERVER_ERROR = 5000;
    const SPACE_NOT_FOUND = 5001;
}
