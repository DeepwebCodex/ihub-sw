<?php

namespace App\Components\Integrations\EuroGamesTech;

abstract class StatusCode
{
    const OK = 1000;
    const DUPLICATE = 1100;
    const OK_DO_REALITY_CHECK = 1300;
    const OK_90_TIME_PROXIMITY_ALERT = 1400;
    const OK_10_PERCENT_CREDIT_LEFT_ALERT = 1500;
    const TIMED_OUT = 2000;
    const INTERNAL_SERVER_ERROR = 3000;
    const INSUFFICIENT_FUNDS = 3100;
    const EXPIRED = 3100;
    const BET_LIMIT_REACHED = 3300;
    const LOSS_LIMIT_REACHED = 3400;
    const SESSION_TIME_LIMIT_REACHED = 3500;
}