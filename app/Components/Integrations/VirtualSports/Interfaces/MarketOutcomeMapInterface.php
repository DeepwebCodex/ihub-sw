<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:27 PM
 */

namespace App\Components\Integrations\VirtualSports\Interfaces;


interface MarketOutcomeMapInterface
{
    public function getParticipantId();

    public function getOutcomeTypeId();

    public function getIParam1() : int;

    public function getIParam2() : int;

    public function getDParam1() : float;

    public function getDParam2() : float;

    public function getCoef() : float;
}