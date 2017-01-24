<?php

namespace App\Components\Integrations\VirtualSports\Interfaces;

interface EventProcessorInterface
{
    public function create(array $eventData) : bool;

    public static function getEvent(int $eventId) : EventProcessorInterface;

    public function setResult(array $eventData, bool $finish = true);

    public function cancel() : bool;

    public function finish() : bool;

    public function stopBets() : bool;

    /**
     * @return int|null
     */
    public function getEventId();
}