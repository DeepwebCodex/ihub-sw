<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
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
     * @param $config
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
    public function setProgress(int $eventVbId, int $sportId, string $progressName)
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
     * @return bool
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setNoMoreBets(int $eventId)
    {
        $statusName = 'inprogress';
        \DB::connection('line')->transaction(function () use ($statusName, $eventId) {
            $this->updateMarketEvent($eventId);
            $this->createStatusDesc($statusName, $eventId);
        });
        return $this->sendAmqpMessage($eventId, $statusName);
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function updateMarketEvent($eventId)
    {
        $resUpdate = (new Market)->updateMarketEvent($eventId, 'yes');
        if (!$resUpdate) {
            throw new ApiHttpException(400, 'update_market_n');
        }
    }

    /**
     * @param string $statusName
     * @param int $eventId
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function createStatusDesc(string $statusName, int $eventId)
    {
        $statusDescParams = [
            'status_type' => $statusName,
            'name' => $statusName,
            'event_id' => $eventId
        ];
        $statusDescModel = new StatusDesc($statusDescParams);
        if (!$statusDescModel->save()) {
            throw new ApiHttpException(400, "Can't insert status_desc");
        }
    }

    /**
     * @param $eventId
     * @param $status
     * @return mixed
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function sendAmqpMessage($eventId, $status)
    {
        $msg = json_encode(['type' => $status, 'data' => ['event_id' => $eventId]]);
        return app('AmqpService')->sendMsg(
            $this->getConfigOption('amqp.exchange'),
            $this->getConfigOption('amqp.key') . $eventId,
            $msg
        );
    }

    /**
     * @param $eventId
     * @return bool
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setFinishedEvent($eventId)
    {
        $statusName = 'finished';
        if ($this->processEvent($eventId, $statusName) === 'ok') {
            return $this->sendAmqpMessage($eventId, $statusName);
        }
        throw new ApiHttpException(400, 'cant_calculate_bet');
    }

    /**
     * @param int $eventId
     * @param string $statusName
     * @return mixed
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function processEvent($eventId, $statusName)
    {
        \DB::connection('line')->transaction(function () use ($statusName, $eventId) {
            $this->updateMarketEvent($eventId);
            ResultGame::updateApprove($eventId);
            $this->createStatusDesc($statusName, $eventId);
        });
        return $this->sendMessageApprove($eventId);
    }

    /**
     * @param $eventId
     * @return string
     */
    protected function sendMessageApprove($eventId)
    {
        //$this->CI->load->config('all');
        //$routingKey = $this->CI->config->item('calc_rt');
        $routingKey = 'calc';

        $val = Redis::get('calc_event:' . $eventId);
        if ($val !== 'calc_inprogress') {
            $exchange = 'calculator';
            $msg = json_encode(['events' => [(int)$eventId]]);

            $response = app('AmqpService')->sendMsg($exchange, $routingKey, $msg);

            if ($response === true) {
                return 'ok';
            }
            return 'NotResponse';
        }
        return 'Event now calc!';
    }

    /**
     * @param $eventId
     * @return bool
     * @throws \App\Exceptions\Api\ApiHttpException|\Exception
     */
    protected function setCancelledEvent($eventId)
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
            return true;
        }
        throw new ApiHttpException(400, 'cant_calculate_bet');
    }

    /**
     * @return mixed
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
