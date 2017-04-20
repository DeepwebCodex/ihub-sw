<?php

namespace App\Components\Integrations\MrSlotty;


abstract class StatusCode
{
    const OK = "ERR000";
    const INTERNAL_SERVER_ERROR = "ERR001";
    const INVALID_SIGNATURE = "ERR006";
    const USER_NOT_FOUND = "ERR005";
    const DUPLICATE = "ERR007";
    const NO_MONEY = "ERR003";
    const INVALID_SUM = "ERR004";
}