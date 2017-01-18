<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\InspiredVirtualGaming\Modules\CategoryService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\DataMapper;
use App\Components\Integrations\InspiredVirtualGaming\Modules\EventService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\OutcomeService;
use App\Components\Integrations\InspiredVirtualGaming\Modules\TournamentService;
use App\Components\Integrations\VirtualSports\Result;
use App\Components\Traits\ConfigTrait;
use App\Models\Line\Category;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\MarketTemplate;
use App\Models\Line\Outcome;
use App\Models\Line\OutcomeType;
use App\Models\Line\ResultGame;
use App\Models\Line\ResultGameTotal;
use App\Models\Line\StatusDesc;
use App\Models\Line\Tournament;
use Illuminate\Database\Eloquent\Collection;

class EventResult
{
    use ConfigTrait;

    protected $eventData;
    protected $eventType;
    protected $requestData;

    protected $eventParticipants;
    private $eventId;

    public function __construct(array $data, int $eventId)
    {
        $this->config = config('integrations.inspired');

        $this->requestData = $data;

        $this->eventData = array_get($this->requestData, 'event', []);

        $this->eventType = (int) array_get($this->eventData, 'EventType');

        $this->eventId = $eventId;
    }

    public function process()
    {
        $dataMap = new DataMapper($this->eventData, (int) array_get($this->eventData, 'EventType'));

        $event = Event::findById($this->eventId);

        $participants = $event->preGetParticipant($event->id);

        $resultType = $event->preGetPeriodStart($event->id, 'prebet');

        $results = $dataMap->getMappedResults();

        if(empty($results)) {
            throw new \RuntimeException("No valid event results");
        }

        foreach ($results as $result) {

            $resultGame = ResultGame::create([
                'event_id'              => $event->id,
                'scope_data_id'         => array_get($result, 'game_result_scope_id', $this->getConfigOption('sports.' . $this->eventType . '.game_result_scope_id')),
                'result_type_id'        => data_get($resultType, '0.id'),
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

        if(! ResultGameTotal::updateResultGameTotal([
            'result_total'      => $dataMap->getTotalResult($results, $participants),
            'result_type_id'    => data_get($resultType, '0.id'),
            'result_total_json' => json_encode(array_merge([
                'result_type_id' => data_get($resultType, '0.id')
            ], $dataMap->getTotalResultForJson($results, $participants)))
        ], $event->id) ) {
            throw new \RuntimeException("Unable to update result game total for event {$event->id}");
        }

        if(! (new Market())->suspendMarketEvent($event->id))
        {
            throw new \RuntimeException("Unable to suspend market event {$event->id}");
        }

        if(! StatusDesc::create([
            'status_type' => StatusDesc::STATUS_FINISHED,
            'name' => StatusDesc::STATUS_FINISHED,
            'event_id' => $event->id
        ])) {
            throw new \RuntimeException("Can't insert status_desc");
        }

        if(! ResultGame::updateApprove($event->id)) {
            throw new \RuntimeException("Unable to update approve for event {$event->id}");
        }
    }
}