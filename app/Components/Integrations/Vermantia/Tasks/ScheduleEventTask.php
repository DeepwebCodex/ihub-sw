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

final class ScheduleEventTask extends BaseSchedulerTask
{
    private $retries = 3;
    private $eventData;

    public function __construct(array $eventData)
    {

        $this->eventData = $eventData;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    public function handle()
    {
        $command = "vermantia:process-event";

        $this->runCommand($command, [
            json_encode($this->eventData),
            $this->currentAttempt
        ]);
    }

    public function failing(FailedTaskException $e, int $attempt = 0)
    {

    }
}