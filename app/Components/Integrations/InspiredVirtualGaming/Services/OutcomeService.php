<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 11:52 AM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Services;


use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapDoubleChances;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapEuropeanHandicaps;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapForecastData;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapGoals;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapOverUnders;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapPlayerWinOutcomes;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapRacer;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapScoreBetOutcomes;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapScores;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapTotalPointsOutcomes;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapWdls;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapWinnerOddEven;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapWinnerOneOfTwo;
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MapWinnerYesNo;

class OutcomeService extends \App\Components\Integrations\VirtualSports\Services\OutcomeService
{
    protected $mappingRegistry = [
        'wdls' => MapWdls::class,
        'PlayerWinOutcomes' => MapPlayerWinOutcomes::class,
        'doubleChances' => MapDoubleChances::class,
        'scores' => MapScores::class,
        'goals' => MapGoals::class,
        'TotalPointsOutcomes' => MapTotalPointsOutcomes::class,
        'ScoreBetOutcomes' => MapScoreBetOutcomes::class,
        'winnerOddEven' => MapWinnerOddEven::class,
        'racer' => MapRacer::class,
        'winnerYesNo' => MapWinnerYesNo::class,
        'forecastData' => MapForecastData::class,
        'winnerOneOfTwo' => MapWinnerOneOfTwo::class,
        'overUnders' => MapOverUnders::class,
        'europeanHandicaps' => MapEuropeanHandicaps::class
    ];
}