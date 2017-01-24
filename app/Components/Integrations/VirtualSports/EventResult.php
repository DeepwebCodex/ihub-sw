<?php

namespace App\Components\Integrations\VirtualSports;


use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Traits\ConfigTrait;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\ResultGame;
use App\Models\Line\ResultGameTotal;
use App\Models\Line\StatusDesc;

abstract class EventResult
{
    use ConfigTrait;

    protected $dataMapper;

    protected $eventType;

    private $eventId;

    public function __construct(int $eventId, DataMapperInterface $dataMapper)
    {
        $this->eventId = $eventId;
        $this->dataMapper = $dataMapper;
    }

    public function process() : int
    {
        $event = Event::findById($this->eventId);

        $participants = $event->preGetParticipant($event->id);

        $resultType = $event->preGetPeriodStart($event->id, 'prebet');

        $results = $this->dataMapper->getMappedResults();

        if(empty($results)) {
            throw new \RuntimeException("No valid event results");
        }

        foreach ($results as $result) {

            $resultGame = ResultGame::create([
                'event_id'              => $event->id,
                'scope_data_id'         => array_get($result, 'game_result_scope_id', $this->getConfigOption('sports.' . $this->eventType . '.game_result_scope_id')),
                'result_type_id'        => array_get($result, 'ResultTypeId', data_get($resultType, '0.id')),
                'event_particpant_id'   => data_get($participants, array_get($result, 'num') . '.id'),
                'amount'                => array_get($result, 'amount'),
                'result_time'           => 0,
                'approve'               => 'yes',
                'staff_id'              => $this->getConfigOption('user_id')
            ]);

            if(! $resultGame) {
                throw new \RuntimeException("Unable to create a game result for event {$event->id}");
            }
        }

        $resultTotalJson = $this->dataMapper->getTotalResultForJson($results, $participants);

        if(!empty($resultTotalJson)) {
            $resultTotalJson = json_encode(array_merge([
                'result_type_id' => $this->dataMapper->getResultTypeId(data_get($resultType, '0.id'))
            ], $resultTotalJson));
        } else {
            $resultTotalJson = '';
        }

        if(! ResultGameTotal::updateResultGameTotal([
            'result_total'      => $this->dataMapper->getTotalResult($results, $participants),
            'result_type_id'    => $this->dataMapper->getResultTypeId(data_get($resultType, '0.id')),
            'result_total_json' => $resultTotalJson
        ], $event->id) ) {
            throw new \RuntimeException("Unable to update result game total for event {$event->id}");
        }

        if(! ResultGame::updateApprove($event->id)) {
            throw new \RuntimeException("Unable to update approve for event {$event->id}");
        }

        $this->eventId = $event->id;

        return (int) $event->id;
    }

    public function finishEvent()
    {
        if(! (new Market())->suspendMarketEvent($this->eventId))
        {
            throw new \RuntimeException("Unable to suspend market event {$this->eventId}");
        }

        if(! StatusDesc::create([
            'status_type' => StatusDesc::STATUS_FINISHED,
            'name' => StatusDesc::STATUS_FINISHED,
            'event_id' => $this->eventId
        ])) {
            throw new \RuntimeException("Can't insert status_desc");
        }
    }
}