<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 3/31/17
 * Time: 10:49 AM
 */

namespace App\Components\Integrations\Vermantia;




use ReflectionClass;

final class VermantiaDirectory
{
    const Greyhounds = "PlatinumHounds";
    const DashingDerby = "DashingDerby";
    const HarnessRacing = "HarnessRacing";
    const Football = "Football";
    const FootballLeague = "FootballLeague";
    const FootballLeagueWeek = "FootballLeagueWeek";
    const FootballLeagueMatch = "FootballLeagueMatch";
    const TableTennis = "TableTennis";
    const Badminton = "Badminton";
    const HorseRacingRoulette = "HorseRacingRoulette";
    const BasketballLeague = "BasketballLeague";
    const BasketballLeagueWeek = "BasketballLeagueWeek";
    const BasketballLeagueMatch = "BasketballLeagueMatch";
    const GreyhoundsCompact = "PreRecDogs";
    const DashingDerbyCompact = "PreRecHorses";
    const SlotCarRacing = "PreRecordedSlotCarRacing";
    const Archery = "Archery";
    const CycleRacing = "CycleRacing";
    const SteepleChaseRacing = "SteepleChase";
    const MotorRacing = "MotorRacing";
    const SingleSeaterMotorRacing = "SingleSeaterMotorRacing";
    const KenoSmartPlayKeno = "Keno/SmartPlayKeno";
    const SpinAndWin = "SpinAndWin";
    const Roulette = "Roulette";
    const HorsesEqualOdds = "HorsesEqual";
    const SpeedSkating = "SpeedSkating";
    const SingleSeaterMotorRacingCompact = "PreRecSSMotorRacing";

    public static function eventTypesList() : array
    {
        $oClass = new ReflectionClass(__CLASS__);
        $eventTypes = $oClass->getConstants();

        return $eventTypes;
    }

    public static function eventNodesList() : array
    {
        return [
            'RaceEvent',
            'ArcheryEvent',
            'BasketballLeagueEvent',
            'FootballEvent',
            'FootballLeagueEvent',
            'KenoEvent',
            'SpinAndWinEvent',
            'RacingRouletteEvent',
            'PlayerVsPlayerEvent'
        ];
    }

    public static function getEnabledSports() : array
    {
        return collect(config('integrations.vermantia.sports'))->keys()->toArray();
    }
}