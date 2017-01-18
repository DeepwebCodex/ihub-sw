<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:09 AM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\SportMapping;

abstract class BaseSportDataMap
{
    protected $eventData;

    public function __construct(array $eventData)
    {
        $this->eventData = $eventData;
    }
}