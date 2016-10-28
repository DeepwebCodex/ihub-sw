<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Components\Integrations\VirtualSports\Participant;
use App\Exceptions\Api\ApiHttpException;
use App\Models\Line\Event;
use App\Models\Line\Market;
use App\Models\Line\ResultGame;
use App\Models\Line\Sport;
use App\Models\Line\StatusDesc;
use App\Models\VirtualBoxing\EventLink;
use Illuminate\Support\Facades\Redis;

/**
 * Class ProgressService
 * @package App\Components\Integrations\VirtualBoxing
 */
class ProgressService
{
    use ConfigTrait;

    /**
     * @var int
     */
    protected $eventId;

    /**
     * ResultService constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param int $eventVbId
     * @param int $sportId
     * @param string $progressName
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    public function setProgress(int $eventVbId, int $sportId, string $progressName):void
    {
        $event = EventLink::getByVbId($eventVbId);
        if (!$event) {
            throw new ApiHttpException(200, 'havent_event');
        }
        $eventId = $event->event_id;
        $this->eventId = $eventId;
        if ((new Sport())->checkSportEventExists($sportId, $eventId) === false) {
            throw new ApiHttpException(400, 'wrong_sport_id_for_event');
        }
        switch ($progressName) {
            case 'N':
                $this->setNoMoreBets($eventId);
                break;
            case 'Z':
                $this->setFinishedEvent($eventId);
                break;
            case 'V':
                $this->setCancelledEvent($eventId);
                break;
            default:
                throw new ApiHttpException(400, 'miss_element');
        }
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setNoMoreBets(int $eventId):void
    {
        $statusName = 'inprogress';
        \DB::connection('line')->transaction(function () use ($statusName, $eventId) {
            $this->suspendMarketEvent($eventId);
            $this->createStatusDesc($statusName, $eventId);
        });
        $this->sendAmqpMessage($eventId, $statusName);
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function suspendMarketEvent(int $eventId):void
    {
        $resUpdate = (new Market)->suspendMarketEvent($eventId);
        if (!$resUpdate) {
            throw new ApiHttpException(400, 'update_market_n');
        }
    }

    /**
     * @param string $statusName
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function createStatusDesc(string $statusName, int $eventId):void
    {
        $statusDescModel = new StatusDesc([
            'status_type' => $statusName,
            'name' => $statusName,
            'event_id' => $eventId
        ]);
        if (!$statusDescModel->save()) {
            throw new ApiHttpException(400, "Can't insert status_desc");
        }
    }

    /**
     * @param int $eventId
     * @param string $status
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function sendAmqpMessage(int $eventId, string $status):void
    {
        $msg = json_encode(['type' => $status, 'data' => ['event_id' => $eventId]]);
        $sendResult = app('AmqpService')->sendMsg(
            $this->getConfigOption('amqp.exchange'),
            $this->getConfigOption('amqp.key') . $eventId,
            $msg
        );
        if (!$sendResult) {
            throw new ApiHttpException(400, 'cant_calculate_bet');
        }
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setFinishedEvent(int $eventId):void
    {
        $statusName = 'finished';
        if ($this->processEvent($eventId, $statusName) === 'ok') {
            $this->sendAmqpMessage($eventId, $statusName);
            return;
        }
        throw new ApiHttpException(400, 'cant_calculate_bet');
    }

    /**
     * @param int $eventId
     * @param string $statusName
     * @return string
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function processEvent(int $eventId, string $statusName):string
    {
        \DB::connection('line')->transaction(function () use ($statusName, $eventId) {
            $this->suspendMarketEvent($eventId);
            ResultGame::updateApprove($eventId);
            $this->createStatusDesc($statusName, $eventId);
        });
        return $this->sendMessageApprove($eventId);
    }

    /**
     * @param int $eventId
     * @return string
     */
    protected function sendMessageApprove(int $eventId):string
    {
        //$this->CI->load->config('all');
        //$routingKey = $this->CI->config->item('calc_rt');
        $routingKey = 'calc';

        $val = Redis::get('calc_event:' . $eventId);
        if ($val !== 'calc_inprogress') {
            $exchange = 'calculator';
            $msg = json_encode(['events' => [$eventId]]);

            $response = app('AmqpService')->sendMsg($exchange, $routingKey, $msg);

            if ($response === true) {
                return 'ok';
            }
            return 'NotResponse';
        }
        return 'Event now calc!';
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setCancelledEvent(int $eventId):void
    {
        $event = Event::findById($eventId);
        if ($event === false) {
            throw new ApiHttpException(400, "Can't find event");
        } elseif ($event && $event->status_type === 'finished') {
            throw new ApiHttpException(400, 'cant_void_finished');
        }

        $statusName = 'cancelled';
        if ($this->processEvent($eventId, $statusName) === 'ok') {
            $this->sendAmqpMessage($eventId, $statusName);
            return;
        }
        throw new ApiHttpException(400, 'cant_calculate_bet');
    }

    /**
     * @return int
     */
    public function getEventId():int
    {
        return $this->eventId;
    }
}
