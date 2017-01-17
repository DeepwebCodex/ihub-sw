<?php

namespace App\Components\Integrations\BetGames;

abstract class StatusCode
{
    const OK = 0;
    const SIGNATURE = 1;
    const TIME = 2;
    const TOKEN = 3;
    const INSUFFICIENT_FUNDS = 4;
    const BAD_OPERATION_ORDER = 700;
    /** not implemented in Bet Games API */
    const UNKNOWN = -1;
    const DUPLICATED_WIN = -10;
}