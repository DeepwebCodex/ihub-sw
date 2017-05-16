<?php

namespace App\Components\Integrations\WirexGaming;

/**
 * Class StatusCode
 * @package App\Components\Integrations\WirexGaming
 */
abstract class StatusCode
{
    const SYSTEM_ERROR_CODE = -1;
    const AMOUNT_NOT_AVAILABLE_CODE = 1;
    const USER_NOT_AUTHORIZED_CODE = 2;
    const WRONG_SERVICE_CALLED_CODE = 3;
}
