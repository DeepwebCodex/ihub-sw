<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 11:52 AM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;


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
use App\Components\Integrations\InspiredVirtualGaming\MarketOutcomeMapping\MarketOutcomeMapInterface;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use Illuminate\Database\Eloquent\Collection;

class OutcomeService
{
    /**
     * @var array
     */
    private $outcome;
    /**
     * @var MarketTemplate
     */
    private $marketTemplate;
    /**
     * @var Collection
     */
    private $outcomeTypes;
    /**
     * @var Market
     */
    private $marketModel;
    /**
     * @var Collection
     */
    private $eventParticipants;

    private $market;

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

    private $mappedMarketsWithOutcomes;

    /**
     * OutcomeService constructor.
     * @param string $market
     * @param array $outcome
     * @param array $mappedMarketsWithOutcomes
     * @param MarketTemplate $marketTemplate
     * @param Collection $outcomeTypes
     * @param Market $marketModel
     * @param Collection $eventParticipants
     */
    public function __construct(string $market, array $outcome, array $mappedMarketsWithOutcomes, MarketTemplate $marketTemplate, Collection $outcomeTypes, Market $marketModel, Collection $eventParticipants)
    {
        $this->outcome = $outcome;
        $this->marketTemplate = $marketTemplate;
        $this->outcomeTypes = $outcomeTypes;
        $this->marketModel = $marketModel;
        $this->eventParticipants = $eventParticipants;
        $this->market = $market;
        $this->mappedMarketsWithOutcomes = $mappedMarketsWithOutcomes;
    }

    public function resolve() : Outcome
    {
        try {
            /** @var MarketOutcomeMapInterface $mapper */
            $mapper = $this->getMapper();
        } catch (\RuntimeException $exception) {

            if($exception->getCode() == 6667) {
                return new Outcome();
            }

            throw $exception;
        }

        $outcome = Outcome::create([
            'event_market_id'       => $this->marketModel->id,
            'event_participant_id'  => $mapper->getParticipantId(), //get participant id
            'outcome_type_id'       => $mapper->getOutcomeTypeId(), //outcome template id
            'coef'                  => $mapper->getCoef(),
            'dparam1'               => $mapper->getDParam1(),
            'dparam2'               => $mapper->getDParam2(),
            'iparam1'               => $mapper->getIParam1(),
            'iparam2'               => $mapper->getIParam2()
        ]);

        if(! $outcome) {
            throw new \RuntimeException("Unable to create outcome of type {$mapper->getOutcomeTypeId()} for market {$this->market}");
        }

        return $outcome;
    }

    protected function getMapper() : MarketOutcomeMapInterface
    {
        $mapperClass = array_get($this->mappingRegistry, $this->market);

        if(!$mapperClass) {
            throw new \RuntimeException("Unable to locate mapper for outcome market {$this->market}", 6667);
        }

        return new $mapperClass($this->outcome, $this->mappedMarketsWithOutcomes, $this->marketTemplate, $this->outcomeTypes, $this->eventParticipants);
    }
}