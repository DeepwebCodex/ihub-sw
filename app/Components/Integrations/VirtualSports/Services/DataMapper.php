<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\VirtualSports\Services;

use App\Components\Integrations\InspiredVirtualGaming\SportMapping\FootballDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\HorsesDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\NumbersDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\TennisDataMap;
use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Integrations\VirtualSports\Interfaces\SportDataMapInterface;

abstract class DataMapper implements DataMapperInterface
{
    protected $eventData;

    protected $eventType;

    protected $mapper;

    protected $mappingRegistry = [
        0 => HorsesDataMap::class,
        1 => HorsesDataMap::class,
        2 => HorsesDataMap::class,
        3 => HorsesDataMap::class,
        4 => FootballDataMap::class,
        5 => NumbersDataMap::class,
        6 => HorsesDataMap::class,
        7 => HorsesDataMap::class,
        8 => TennisDataMap::class
    ];

    public function __construct(array $eventData, $eventType)
    {
        $this->eventData = $eventData;

        $this->eventType = $eventType;

        $this->mapper = $this->getMapper();
    }

    protected function getMapper() : SportDataMapInterface
    {
        $mapperClass = array_get($this->mappingRegistry, $this->eventType);

        if(!$mapperClass) {
            throw new \RuntimeException("Unable to locate mapper for sport {$this->eventType}", 6667);
        }

        return new $mapperClass($this->eventData);
    }

    abstract public function getEventTime();

    abstract public function getEventId();

    public function getEventName() : string
    {
        return $this->mapper->getEventName();
    }

    public function getParticipants() : array
    {
        return $this->mapper->getParticipants();
    }

    public function getMappedResults() : array
    {
        return $this->mapper->getMappedResults();
    }

    public function getTotalResult(array $results, array $participants) : string
    {
        return $this->mapper->getTotalResult($results, $participants);
    }

    public function getTotalResultForJson(array $results, array $participants) : array
    {
        return $this->mapper->getTotalResultForJson($results, $participants);
    }

    abstract public function getMarketsWithOutcomes() : array;

    public function getResultTypeId(int $default) : int
    {
        return $this->mapper->getResultTypeId($default);
    }
}