<?php

namespace App\Console\Commands\Vermantia\Traits;

use Carbon\Carbon;

trait DateParserTrait
{
    protected function getTimeDiff(string $localTime, string $utcTime, Carbon $timeAfterRequest) : int
    {
        $utcDateLocal = new Carbon($localTime);
        $utcDate      = new Carbon($utcTime,'UTC');

        $utcDiff = $utcDate->diff($timeAfterRequest);

        $utcDate = $utcDate->add($utcDiff);

        return $utcDateLocal->diffInSeconds($utcDate, false);
    }
}
