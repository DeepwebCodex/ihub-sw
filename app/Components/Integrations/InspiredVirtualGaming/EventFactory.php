<?php

namespace App\Components\Integrations\InspiredVirtualGaming;

use App\Components\Integrations\VirtualSports\Result;
use App\Components\Traits\ConfigTrait;
use App\Models\InspiredVirtualGaming\EventLink;
use App\Models\InspiredVirtualGaming\MarketLink;
use App\Models\InspiredVirtualGaming\OutcomeLink;
use App\Models\Line\Category;
use App\Models\Line\Event as EventModel;
use App\Models\Line\EventParticipant;
use App\Models\Line\Market;
use App\Models\Line\Outcome;
use App\Models\Line\Participant;
use App\Models\Line\Sportform;
use App\Models\Line\StatusDesc;
use App\Models\Line\Tournament;
use App\Models\Trans\Trans;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventFactory
{
    use ConfigTrait;

    protected $eventData;

    private $category_id;
    private $sportform_prebet_id;
    private $sportform_live_id;
    private $max_bet;
    private $max_payout;
    private $stop_loss;

    private $eventType;
    private $controllerId;

    public function __construct(array $eventData)
    {
        $this->config = config('integrations.inspired');

        $this->eventData = $eventData;

        $this->eventType = (int) array_get($this->eventData, 'EventType');
        $this->controllerId = (int) array_get($this->eventData, 'ControllerId', 105);

        $this->sportform_prebet_id = $this->getConfigOption('sports.'. $this->eventType . '.sportform_prebet_id');
        $this->sportform_live_id = $this->getConfigOption('sports.'. $this->eventType . '.sportform_live_id');
        $this->max_bet = $this->getConfigOption('sports.'. $this->eventType . '.max_bet');
        $this->max_payout = $this->getConfigOption('sports.'. $this->eventType . '.max_payout');
        $this->stop_loss = $this->getConfigOption('sports.'. $this->eventType . '.stop_loss');
    }

    public function create() : bool
    {
        if(!$this->validate())
        {
            return false;
        }

        if(method_exists($this, "processEventType{$this->eventType}")) {
            $this->{"processEventType{$this->eventType}"}();
        } else {
            return false;
        }

        return true;
    }

    protected function resolveCategory()
    {
        $eventName = $this->getConfigOption('sports.'. $this->eventType . '.name');

        if(!($eventName || $this->controllerId)) {
            throw new \RuntimeException("Error resolving category id");
        }

        $sportId = $this->getConfigOption('sport_id');

        $category = Category::findByNameForSport("{$eventName}_{$this->controllerId}", $sportId);

        if ($category === null) {
            $category = new Category([
                'name' => "{$eventName}_{$this->controllerId}",
                'weigh' => 100,
                'enet_id' => null,
                'sport_id' => $sportId,
                'gender' => $this->getConfigOption('gender'),
                'country_id' => $this->getConfigOption('country_id'),
                'slug' => null
            ]);
            if (!$category->save()) {
                throw new \RuntimeException("Unable to save new category");
            }
        }

        return $category;
    }

    protected function processEventType0()
    {
        DB::connection('line')->beginTransaction();

        try {

            //resolve category

            $this->category_id = $this->resolveCategory()->id;

            //CreateTournament if not exists

            $tournamentName = transliterate($this->eventData['CourseName']);

            $tournament = Tournament::findByNameForSport($tournamentName, $this->category_id);

            if(!$tournament) {

                $eventName = (new Trans())->translate($tournamentName);

                $tournament = Tournament::create([
                    'name' => $tournamentName,
                    'weigh' => 100,
                    'enet_id' => null,
                    'category_id' => $this->category_id,
                    'sportform_id' => $this->sportform_prebet_id,
                    'country_id' => $this->getConfigOption('country_id'),
                    'enet_import' => 'no',
                    'import_odds_provider' => 0,
                    'max_bet' => $this->max_bet,
                    'max_payout' => $this->max_payout,
                    'stop_loss' => $this->stop_loss,
                    'margin' => 108,
                    'margin_prebet' => 108,
                    'live_sportform_id' => $this->sportform_live_id,
                    'gender' => $this->getConfigOption('gender'),
                    'user_id' => $this->getConfigOption('user_id'),
                    'sport_union_id' => null,
                    'stop_loss_exp' => 0
                ]);
            }

            if ($tournament) {
                $tournamentId = $tournament->id; //return here
            }

            //Collect participants for event type 0

            $participants = new Collection();

            foreach ($this->eventData['racer'] as $participant)
            {
                $participants->put((int) $participant['Num'], [
                    'name'          => (string) $participant['Name'],
                    'type'          => 'athlete',
                    'country_id'    => $this->getConfigOption('country_id'),
                    'sport_id'      => $this->getConfigOption('sport_id')
                ]);
            }

            //create event

            $eventParticipants = new Collection();

            //create/update participant
            $participants->each(function($participant, $key) use(&$eventParticipants){
                $name = (new Trans())->persistTranslation(array_get($participant, 'name'));
                $participantId = Participant::createOrUpdate(array_merge($participant, ['name' => $name]))->id;
                $eventParticipants->push(array_merge($participant, [
                    'name'              => $name,
                    'participant_id'    => $participantId,
                    'number'            => $key
                ]));
            });

            //persist event into db

            $eventModel = EventModel::create([
                'tournament_id' => $tournamentId,
                'dt'            => $this->eventData['EventTime'],
                'name'          => $eventName,
                'locked'        => 'no',
                'weigh'         => 100,
                'enet_stat_url' => 'none',
                'max_bet'       => $this->max_bet,
                'max_payout'    => $this->max_payout,
                'stop_loss'     => $this->stop_loss
            ]);

            EventLink::create([
                'event_id' => $eventModel->id,
                'event_id_ivg' => (int) $this->eventData['EventId']
            ]);

            //create event_participants

            $eventParticipantsModels = new Collection();

            $eventParticipants->each(function($event_participant) use(&$eventParticipantsModels, $eventModel){
                $eventParticipantsModels->put(
                    $event_participant['number'],
                    EventParticipant::create([
                        array_merge($event_participant, ['event_id' => $eventModel->id])
                    ])
                );
            });

            //init result event ($eventModel->id, "prebet")

            (new Result())->initResultEvent("prebet", $eventModel->id);

            //create market ($this->eventData, $eventModel)

            $marketTemplates = MarketLink::getTemplates((int) $this->eventData['EventId']);

            if(empty($marketTemplates)) {
                throw new \RuntimeException("Market templates not found for event {$this->eventData['EventId']}");
            }

            foreach ($marketTemplates as $marketTemplate) {

                $outcomeTemplates = OutcomeLink::getTemplates($marketTemplate->market_template_id);

                if(empty($outcomeTemplates)) {
                    throw new \RuntimeException("Outcome templates not found for market template {$marketTemplate->market_template_id}");
                }

                $market = Market::create([
                    'event_id'              => $eventModel->id,
                    'market_template_id'    => $marketTemplate->market_template_id,
                    'result_type_id'        => $this->getConfigOption('market.result_type_id'),
                    'max_bet'               => $marketTemplate->max_bet,
                    'max_payout'            => $marketTemplate->max_payout,
                    'stop_loss'             => $marketTemplate->stop_loss,
                    'weight'                => $marketTemplate->weigh,
                    'service_id'            => $this->getConfigOption('service_id'),
                    'user_id'               => $this->getConfigOption('user_id')
                ]);

                foreach ($this->eventData as $name => $outcome) {
                    if(!is_array($outcome)) {
                        continue;
                    }

                    if($name == $marketTemplate->market_template_id_ivg) {

                        if($marketTemplate->nested) {

                            foreach ($outcomeTemplates as $outcomeTemplate) {
                                foreach ($outcome as $outcomeValues) {
                                    //outcome value validation
                                    if(
                                        trim(strtolower($outcomeTemplate->outcome_template_id_ivg)) != trim(strtolower($outcomeValues[$outcomeTemplate->outcome_field]))
                                        ||
                                        $outcomeValues[$outcomeTemplate->coef_field] != "-"
                                    ) {
                                        continue;
                                    }

                                    //get event participant id for outcome

                                    $eventParticipantId = null;

                                    if($marketTemplate->market_type_id == 4) {
                                        $eventParticipantModel = $eventParticipantsModels->get(array_get($outcome, 'Num'));
                                        if($eventParticipantModel) {
                                            $eventParticipantId = $eventParticipantModel->id;
                                        }
                                    }

                                    if(!($outcomeTemplate->participant_num && $outcomeTemplate->participant_num_ivg)) {
                                        $eventParticipantModel = $eventParticipantsModels->get($outcomeTemplate->market_template_id);
                                        if($eventParticipantModel) {
                                            $eventParticipantId = $eventParticipantModel->id;
                                        } else {
                                            if($outcomeTemplate->participant_num) {
                                                $eventParticipantModel = $eventParticipantsModels->get($outcomeTemplate->participant_num);
                                                if($eventParticipantModel) {
                                                    $eventParticipantId = $eventParticipantModel->id;
                                                }
                                            } elseif ($outcomeTemplate->participant_num_ivg) {
                                                $eventParticipantModel = $eventParticipantsModels->get($outcomeTemplate->participant_num_ivg);
                                                if($eventParticipantModel) {
                                                    $eventParticipantId = $eventParticipantModel->id;
                                                }
                                            }
                                        }
                                    } else {
                                        throw new \RuntimeException("Double participant template values");
                                    }

                                    list($iParam1, $iParam2) = $outcomeTemplate->getIParams($this->eventData);

                                    Outcome::create([
                                        'event_market_id'       => $market->id,
                                        'event_participant_id'  => $eventParticipantId,
                                        'outcome_type_id'       => $outcomeTemplate->outcome_template_id,
                                        'coef'                  => array_get($outcomeValues, $outcomeTemplate->coef_field),
                                        'dparam1'               => $outcomeTemplate->dparam1,
                                        'iparam1'               => $iParam1,
                                        'iparam2'               => $iParam2
                                    ]);
                                }
                            }

                        }
                    }
                }
            }

            //suspend market and change status

            if(!(new Market())->resumeMarketEvent($eventModel->id))
            {
                throw new \RuntimeException("Unable to resume market event {$eventModel->id}");
            }

            if(! StatusDesc::create([
                'status_type' => "notstarted",
                'name' => "notstarted",
                'event_id' => $eventModel->id
            ])){
                throw new \RuntimeException("Can't insert status_desc");
            }

        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            return false;
        }

        DB::connection('line')->commit();

        return true;
    }

    protected function processEventType4()
    {
        return $this->processEventType0();
    }

    protected function validate() : bool
    {
        /**@var \Illuminate\Validation\Validator $validation */
        $validation = Validator::make(
            get_object_vars($this),
            [
                'sportform_prebet_id' => 'bail|required|integer',
                'sportform_live_id' => 'bail|required|integer',
                'max_bet' => 'bail|required|integer',
                'max_payout' => 'bail|required|integer',
                'stop_loss' => 'bail|required|integer',
            ]
        );

        if ($validation->fails()) {
            //$validation->messages()->getMessages();
            return false;
        }

        return true;
    }


}