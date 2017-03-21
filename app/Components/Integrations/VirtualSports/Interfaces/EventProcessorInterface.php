<?php

namespace App\Components\Integrations\VirtualSports\Interfaces;

interface EventProcessorInterface
{
    public function create(DataMapperInterface $dataMapper) : bool;

    public static function getEvent(int $eventId) : EventProcessorInterface;

    public function setResult(DataMapperInterface $dataMapper, bool $finish = true);

    public function cancel() : bool;

    public function finish() : bool;

    public function stopBets() : bool;

    /**
     * @return int|null
     */
    public function getEventId();
}