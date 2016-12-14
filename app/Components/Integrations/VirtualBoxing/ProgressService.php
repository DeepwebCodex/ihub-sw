<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\ConfigTrait;
use App\Exceptions\Api\VirtualBoxing\ErrorException;
use App\Models\Line\Event;
use App\Models\Line\Market as MarketModel;
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

    const STATUS_CODE_NO_MORE_BETS = 'N';

    const STATUS_CODE_FINISHED_EVENT = 'Z';

    const STATUS_CODE_CANCELLED_EVENT = 'V';

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
     * @return array
     */
    public static function getAvailableStatusCodes():array
    {
        $reflector = new \ReflectionClass(static::class);
        $constants = $reflector->getConstants();

        $prefix = 'STATUS_CODE_';
        $values = array_filter($constants, function ($key) use ($prefix) {
            return strpos($key, $prefix) !== false;
        }, ARRAY_FILTER_USE_KEY);

        return array_values($values);
    }

    /**
     * @param int $eventVbId
     * @param int $sportId
     * @param string $statusCode
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function setProgress(int $eventVbId, int $sportId, string $statusCode)
    {
        $event = EventLink::getByVbId($eventVbId);
        if (!$event) {
            throw new ErrorException('cant_find_event');
        }
        $eventId = $event->event_id;
        $this->eventId = $eventId;
        if ((new Sport())->checkSportEventExists($sportId, $eventId) === false) {
            throw new ErrorException('wrong_sport_id_for_event');
        }
        switch ($statusCode) {
            case self::STATUS_CODE_NO_MORE_BETS:
                $this->setNoMoreBets($eventId);
                break;
            case self::STATUS_CODE_FINISHED_EVENT:
                $this->setFinishedEvent($eventId);
                break;
            case self::STATUS_CODE_CANCELLED_EVENT:
                $this->setCancelledEvent($eventId);
                break;
            default:
                throw new ErrorException('miss_element');
        }
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function setNoMoreBets(int $eventId)
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
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function suspendMarketEvent(int $eventId)
    {
        $resUpdate = (new MarketModel)->suspendMarketEvent($eventId);
        if (!$resUpdate) {
            throw new ErrorException('update_market_n');
        }
    }

    /**
     * @param string $statusName
     * @param int $eventId
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function createStatusDesc(string $statusName, int $eventId)
    {
        $statusDescModel = new StatusDesc([
            'status_type' => $statusName,
            'name' => $statusName,
            'event_id' => $eventId
        ]);
        if (!$statusDescModel->save()) {
            throw new ErrorException("Can't insert status_desc");
        }
    }

    /**
     * @param int $eventId
     * @param string $status
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function sendAmqpMessage(int $eventId, string $status)
    {
        $msg = json_encode(['type' => $status, 'data' => ['event_id' => $eventId]]);
        $sendResult = app('AmqpService')->sendMsg(
            $this->getConfigOption('amqp.exchange'),
            $this->getConfigOption('amqp.key') . $eventId,
            $msg
        );
        if (!$sendResult) {
            throw new ErrorException('cant_calculate_bet');
        }
    }

    /**
     * @param int $eventId
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function setFinishedEvent(int $eventId)
    {
        $statusName = 'finished';
        if ($this->processEvent($eventId, $statusName) === 'ok') {
            $this->sendAmqpMessage($eventId, $statusName);
            return;
        }
        throw new ErrorException('cant_calculate_bet');
    }

    /**
     * @param int $eventId
     * @param string $statusName
     * @return string
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
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
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function setCancelledEvent(int $eventId)
    {
        $event = Event::findById($eventId);
        if ($event === null) {
            throw new ErrorException("Can't find event");
        } elseif ($event && $event->status_type === 'finished') {
            throw new ErrorException('cant_void_finished');
        }

        $statusName = 'cancelled';
        if ($this->processEvent($eventId, $statusName) === 'ok') {
            $this->sendAmqpMessage($eventId, $statusName);
            return;
        }
        throw new ErrorException('cant_calculate_bet');
    }

    /**
     * @return int
     */
    public function getEventId():int
    {
        return $this->eventId;
    }
}
