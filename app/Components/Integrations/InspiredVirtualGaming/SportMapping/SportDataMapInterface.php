<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/18/17
 * Time: 11:16 AM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\SportMapping;


interface SportDataMapInterface
{
    public function getEventName() : string;

    public function getParticipants() : array;

    public function getMappedResults() : array;

    public function getTotalResult(array $results, array $participants) : string;

    public function getTotalResultForJson(array $results, array $participants) : array;
}