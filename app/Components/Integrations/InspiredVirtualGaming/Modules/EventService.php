<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;


use App\Models\InspiredVirtualGaming\EventLink;
use App\Models\Line\Event;
use App\Models\Line\EventParticipant;
use App\Models\Line\Participant;
use App\Models\Trans\Trans;
use Illuminate\Database\Eloquent\Collection;

class EventService
{

    private $tournamentId;
    private $eventTime;
    private $eventName;
    private $weight;
    private $maxBet;
    private $maxPayout;
    private $stopLoss;
    private $countryId;
    private $sportId;
    private $origEventId;
    private $participants;

    /**
     * @var Collection
    */
    private $eventParticipants;

    public function __construct(
        int $tournamentId,
        string $eventTime,
        string $eventName,
        int $weight,
        float $maxBet,
        float $maxPayout,
        float $stopLoss,
        int $countryId,
        int $sportId,
        $origEventId,
        array $participants
    )
    {

        $this->tournamentId = $tournamentId;
        $this->eventTime = $eventTime;
        $this->eventName = $eventName;
        $this->weight = $weight;
        $this->maxBet = $maxBet;
        $this->maxPayout = $maxPayout;
        $this->stopLoss = $stopLoss;
        $this->countryId = $countryId;
        $this->sportId = $sportId;
        $this->origEventId = $origEventId;
        $this->participants = $participants;

        $this->eventParticipants = new Collection();
    }

    public function resolve() : Event
    {
        $this->createParticipants();

        $name = (new Trans())->translate($this->eventName);

        $eventModel = Event::create([
            'tournament_id' => $this->tournamentId,
            'dt'            => $this->eventTime,
            'name'          => $name,
            'locked'        => 'no',
            'weigh'         => $this->weight,
            'enet_stat_url' => 'none',
            'max_bet'       => $this->maxBet,
            'max_payout'    => $this->maxPayout,
            'stop_loss'     => $this->stopLoss
        ]);

        EventLink::create([
            'event_id' => $eventModel->id,
            'event_id_ivg' => (int) $this->origEventId
        ]);

        if(!$eventModel) {
            throw new \RuntimeException("Unable to create event");
        }

        $this->createEventParticipants($eventModel->id);

        return $eventModel;
    }

    public function getEventParticipants() : Collection
    {
        return $this->eventParticipants;
    }

    protected function createEventParticipants(int $eventId)
    {
        foreach ($this->participants as $key => $participant)
        {
            $participantModel = EventParticipant::create([
                'number'            => data_get($participant, 'number'),
                'participant_id'    => data_get($participant, 'participant_id'),
                'event_id'          => $eventId
            ]);

            if(!$participantModel) {
                throw new \RuntimeException("Unable to create event participant");
            }

            $this->eventParticipants->add($participantModel);
        }
    }

    protected function createParticipants()
    {
        foreach ($this->participants as $key => $participant)
        {
            $name = (new Trans())->translate(data_get($participant, 'name'));

            $participantModel = Participant::createOrUpdate([
                'name'          => $name,
                'type'          => data_get($participant, 'type'),
                'country_id'    => $this->countryId,
                'sport_id'      => $this->sportId
            ]);

            if(!$participantModel) {
                throw new \RuntimeException("Unable to get a participant");
            }

            $this->participants[$key]['participant_id'] = $participantModel->id;
        }
    }
}