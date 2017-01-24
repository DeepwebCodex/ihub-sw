<?php

namespace App\Components\Integrations\VirtualBoxing;

use App\Components\Integrations\VirtualSports\Calculator;
use App\Components\Traits\ConfigTrait;
use App\Exceptions\Api\VirtualBoxing\ErrorException;
use App\Models\Line\Event;
use App\Models\Line\Market as MarketModel;
use App\Models\Line\ResultGame;
use App\Models\Line\Sport;
use App\Models\Line\StatusDesc;
use App\Models\VirtualBoxing\EventLink;

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
     * ProgressService constructor.
     * @param array $config
     * @param int $eventVbId
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    public function __construct(array $config, int $eventVbId)
    {
        $this->config = $config;

        $event = EventLink::getByVbId($eventVbId);
        if (!$event) {
            throw new ErrorException('cant_find_event');
        }
        $this->eventId = $event->event_id;
    }

    /**
     * @param string $statusCode
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \RuntimeException
     */
    public function setProgress(string $statusCode)
    {
        $sportId = $this->getConfigOption('sport_id');
        if ((new Sport)->checkSportEventExists($sportId, $this->eventId) === false) {
            throw new ErrorException('wrong_sport_id_for_event');
        }
        switch ($statusCode) {
            case self::STATUS_CODE_NO_MORE_BETS:
                $this->setNoMoreBets();
                break;
            case self::STATUS_CODE_FINISHED_EVENT:
                $this->setFinishedEvent();
                break;
            case self::STATUS_CODE_CANCELLED_EVENT:
                $this->setCancelledEvent();
                break;
            default:
                throw new ErrorException('miss_element');
        }
    }

    /**
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \RuntimeException
     */
    protected function setNoMoreBets()
    {
        $statusName = 'inprogress';
        \DB::connection('line')->transaction(function () use ($statusName) {
            $this->suspendMarketEvent();
            $this->createStatusDesc($statusName);
        });
        $this->sendAmqpMessage($statusName);
    }

    /**
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function suspendMarketEvent()
    {
        $resUpdate = (new MarketModel)->suspendMarketEvent($this->eventId);
        if (!$resUpdate) {
            throw new ErrorException('update_market_n');
        }
    }

    /**
     * @param string $statusName
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function createStatusDesc(string $statusName)
    {
        $statusDescModel = new StatusDesc([
            'status_type' => $statusName,
            'name' => $statusName,
            'event_id' => $this->eventId
        ]);
        if (!$statusDescModel->save()) {
            throw new ErrorException("Can't insert status_desc");
        }
    }

    /**
     * @param string $status
     * @return void
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \RuntimeException
     */
    protected function sendAmqpMessage(string $status)
    {
        $msg = json_encode(['type' => $status, 'data' => ['event_id' => $this->eventId]]);
        $sendResult = app('AmqpService')->sendMsg(
            $this->getConfigOption('amqp.exchange'),
            $this->getConfigOption('amqp.key') . $this->eventId,
            $msg
        );
        if (!$sendResult) {
            throw new ErrorException('cant_calculate_bet');
        }
    }

    /**
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \RuntimeException
     */
    protected function setFinishedEvent()
    {
        $statusName = 'finished';
        if ($this->processEvent($statusName) === 'ok') {
            $this->sendAmqpMessage($statusName);
            return;
        }
        throw new ErrorException('cant_calculate_bet');
    }

    /**
     * @param string $statusName
     * @return string
     * @throws \Exception
     */
    protected function processEvent(string $statusName):string
    {
        \DB::connection('line')->transaction(function () use ($statusName) {
            $this->suspendMarketEvent();
            ResultGame::updateApprove($this->eventId);
            $this->createStatusDesc($statusName);
        });
        return Calculator::sendMessageApprove($this->eventId);
    }

    /**
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     * @throws \App\Exceptions\ConfigOptionNotFoundException
     * @throws \RuntimeException
     */
    protected function setCancelledEvent()
    {
        $event = Event::findById($this->eventId);
        if ($event === null) {
            throw new ErrorException("Can't find event");
        }
        if ($event->status_type === 'finished') {
            throw new ErrorException('cant_void_finished');
        }

        $statusName = 'cancelled';
        if ($this->processEvent($statusName) === 'ok') {
            $this->sendAmqpMessage($statusName);
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
