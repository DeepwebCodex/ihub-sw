<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\VirtualSports\Interfaces;

use App\Components\Integrations\InspiredVirtualGaming\SportMapping\FootballDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\HorsesDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\NumbersDataMap;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\SportDataMapInterface;
use App\Components\Integrations\InspiredVirtualGaming\SportMapping\TennisDataMap;
use Stringy\StaticStringy as S;

interface DataMapperInterface
{
    public function getEventType();

    public function getEventTime();

    public function getEventId();

    public function getEventName();

    public function getTournamentName();

    public function getParticipants() : array;

    public function getMappedResults() : array;

    public function getTotalResult(array $results, array $participants) : string;

    public function getTotalResultForJson(array $results, array $participants) : array;

    public function getMarketsWithOutcomes() : array;

    public function getResultTypeId(int $default) : int;

    public function getRawData() : array;
}