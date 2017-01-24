<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/6/17
 * Time: 12:13 PM
 */

namespace App\Components\Integrations\VirtualSports;


use App\Models\Line\EventParticipant;
use App\Models\Line\MarketTemplate;
use App\Models\Line\OutcomeType;
use Illuminate\Database\Eloquent\Collection;


abstract class BaseMarketOutcomeMapper
{

    protected $outcome;
    /**
     * @var MarketTemplate
     */
    protected $marketTemplate;
    /**
     * @var Collection
     */
    protected $outcomeTypes;
    /**
     * @var Collection
     */
    protected $eventParticipants;

    /**
     * @var OutcomeType
     */
    protected $outcomeType;

    protected $outcomeConfig = [

    ];

    protected $outcomeTypeMap = [

    ];

    const I_PARAM_1 = 'iparam1';
    const I_PARAM_2 = 'iparam2';
    const D_PARAM_1 = 'dparam';
    const D_PARAM_2 = 'dparam';

    private $marketTypesParams = [
        0  => [ null ],
        1  => [ 'dparam'  => false ],
        2  => [ 'iparam1' => false, 'iparam2' => false ],
        3  => [ 'dparam'  => false, 'participant' => true ],
        4  => [ 'participant' => true ],
        5  => [ 'dparam'  => false ],
        6  => [ 'iparam1' => true, 'iparam2' => true, 'dparam' => false ],
        7  => [ 'dparam'  => false, 'participant' => true ],
        8  => [ 'dparam'  => false, 'participant' => true ],
        9  => [ 'iparam1' => true, 'dparam'  => false ],
        10 => [ 'dparam'  => false, 'participant' => true ],
        11 => [ 'iparam1' => false, 'iparam2' => false, 'participant' => true ]
    ];

    protected $mappedMarketsWithOutcomes;

    public function __construct(array $outcome, array $mappedMarketsWithOutcomes, MarketTemplate $marketTemplate, Collection $outcomeTypes, Collection $eventParticipants)
    {
        $this->outcome = $outcome;


        $this->marketTemplate = $marketTemplate;
        $this->outcomeTypes = $outcomeTypes;
        $this->eventParticipants = $eventParticipants;

        $this->outcomeType = $this->getOutcomeType();

        $this->mappedMarketsWithOutcomes = $mappedMarketsWithOutcomes;
    }

    protected function getOutcomeType() : OutcomeType
    {
        $outcomeName = array_get($this->outcome, array_get($this->outcomeConfig, 'outcomeFiled'));

        $outcomeType = $this->outcomeTypes->where('id', array_get($this->outcomeTypeMap, $outcomeName))->first();

        if($outcomeType === null) {
            $this->failedToGetOutcomeType();
        }

        return $outcomeType;
    }

    protected function failedToGetOutcomeType() {
        throw new \RuntimeException("Unable to locate outcome");
    }

    private function isParticipantRequired(int $marketTypeId) : bool
    {
        return (bool) array_get($this->marketTypesParams, $marketTypeId . '.participant');
    }

    protected function isParamRequired(int $marketTypeId, int $marketTypeCount, string $param) : bool
    {
        $param = array_get($this->marketTypesParams, $marketTypeId. '.' . $param);

        if($param === null) {
            return false;
        }

        if($param === false && $marketTypeCount > 0) {
            return true;
        }

        return $param;
    }


    public function getParticipantId()
    {
        if(! $this->outcomeType) {
            return null;
        }

        $numParticipant = (int) $this->outcomeType->participant_num;

        if($numParticipant === null) {
            if ($this->isParticipantRequired($this->marketTemplate->market_type_id) === false) {
                return null;
            }

            return null;
        }

        /**@var EventParticipant $eventParticipant*/
        $eventParticipant = $this->eventParticipants->where('number', $numParticipant)->first();

        if(!$eventParticipant) {
            return null;
        }

        return $eventParticipant->id;
    }

    public function getOutcomeTypeId()
    {
        if($this->outcomeType) {
            return $this->outcomeType->id;
        }

        return null;
    }

    public function getCoef() : float
    {
        $coefFieldName = array_get($this->outcomeConfig, 'coefFiled');

        return (float) array_get($this->outcome, $coefFieldName);
    }

    public function getIParam1(): int
    {
        return 0;
    }

    public function getIParam2() : int
    {
        return 0;
    }

    public function getDParam1() : float
    {
        return 0;
    }

    public function getDParam2() : float
    {
        return 0;
    }
}