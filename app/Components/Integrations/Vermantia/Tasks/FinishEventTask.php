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

final class FinishEventTask extends BaseSchedulerTask
{
    private $retries = 3;
    private $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function getMaxRetries(): int
    {
        return $this->retries;
    }

    public function handle()
    {
        $command = "vermantia:finish-event";

        $this->runCommand($command, [
            $this->eventId
        ]);
    }

    public function failing(FailedTaskException $e)
    {

    }
}