<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:09 AM
 */

namespace App\Components\Integrations\VirtualSports;

abstract class BaseSportDataMap
{
    protected $eventData;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
    }

    public function getResultTypeId(int $default): int
    {
        return $default;
    }
}