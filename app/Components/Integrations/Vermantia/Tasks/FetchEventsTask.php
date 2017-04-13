<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 3/31/17
 * Time: 10:49 AM
 */

namespace App\Components\Integrations\Vermantia\Tasks;

use iHubGrid\DynamicScheduler\BaseSchedulerTask;
use iHubGrid\DynamicScheduler\Exceptions\FailedTaskException;

final class FetchEventsTask extends BaseSchedulerTask
{
    private $retries = 3;
    private $hours;

    public function __construct(int $hours)
    {
        $this->hours = $hours;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    public function handle()
    {
        $command = "vermantia:fetch-events";

        $this->runCommand($command, [
            $this->hours
        ]);
    }

    public function failing(FailedTaskException $e)
    {

    }
}