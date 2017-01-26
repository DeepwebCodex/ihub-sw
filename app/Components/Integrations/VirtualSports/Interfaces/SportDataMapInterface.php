<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:16 AM
 */

namespace App\Components\Integrations\VirtualSports\Interfaces;


interface SportDataMapInterface
{
    public function getEventName() : string;

    public function getTournamentName() : string;

    public function getParticipants() : array;

    public function getMappedResults() : array;

    public function getTotalResult(array $results, array $participants) : string;

    public function getTotalResultForJson(array $results, array $participants) : array;

    public function getResultTypeId(int $default) : int;
}