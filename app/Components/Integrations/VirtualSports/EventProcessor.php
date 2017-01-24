<?php

namespace App\Components\Integrations\VirtualSports;

use App\Components\Integrations\VirtualSports\Interfaces\EventBuilderInterface;
use App\Components\Integrations\VirtualSports\Interfaces\EventResultInterface;
use App\Models\Line\Market;
use App\Models\Line\ResultGame;
use App\Models\Line\StatusDesc;
use Illuminate\Support\Facades\DB;

abstract class EventProcessor
{
    protected $eventId;

    protected $eventBuilderClass;
    protected $eventResultClass;

    protected $amqpExchange;
    protected $amqpKey;

    public function __construct(int $eventId = null)
    {
        $this->eventId = $eventId;
    }

    public function create(array $eventData) : bool
    {
        /**@var EventBuilderInterface $eventBuilder*/
        $eventBuilder = new $this->eventBuilderClass($eventData);

        DB::connection('line')->beginTransaction();
        DB::connection('trans')->beginTransaction();

        try
        {
            $this->eventId = $eventBuilder->create();
        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            DB::connection('trans')->rollBack();

            app('AppLog')->warning([
                'message' => $exception->getMessage()
            ], null, 'event-failed');

            return false;
        }
        DB::connection('trans')->commit();
        DB::connection('line')->commit();


        return true;
    }

    public function setResult(array $eventData, bool $finish = true)
    {
        DB::connection('line')->beginTransaction();
        try {

            if($finish) {
                if (!ResultGame::isResultsApproved($this->eventId)) {
                    $this->sendMessageFinished();
                }
            }

            /**@var EventResultInterface $eventResult*/
            $eventResult = new $this->eventResultClass($eventData, $this->eventId);

            $this->eventId = $eventResult->process();

            if($finish) {
                $eventResult->finishEvent();
                $this->sendMessageFinished();
            }

        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            throw $exception;
        }
        DB::connection('line')->commit();
    }

    public function cancel() : bool
    {
        if(!$this->eventId) {
            return false;
        }

        $result = ResultGame::getResult($this->eventId);

        if($result !== null) {

            DB::connection('line')->beginTransaction();

            try {
                $this->suspendMarket();
                $this->createStatusDesc(StatusDesc::STATUS_CANCELLED);
                $this->updateGameResult();
            } catch (\Exception $exception) {
                DB::connection('line')->rollBack();
                throw $exception;
            }

            DB::connection('line')->commit();

            if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
            {
                throw new \RuntimeException("Unable to send approve");
            }

            $this->sendAmQpMessage(StatusDesc::STATUS_CANCELLED);
        }

        return true;
    }

    public function stopBets() : bool
    {
        if(!$this->eventId) {
            return false;
        }

        DB::connection('line')->beginTransaction();

        try {
            $this->suspendMarket();
            $this->createStatusDesc(StatusDesc::STATUS_IN_PROGRESS);
        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            throw $exception;
        }

        DB::connection('line')->commit();

        if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
        {
            throw new \RuntimeException("Unable to send approve");
        }

        $this->sendAmQpMessage(StatusDesc::STATUS_IN_PROGRESS);

        return true;
    }

    public function finish() : bool
    {
        if(!$this->eventId) {
            return false;
        }

        DB::connection('line')->beginTransaction();

        try {
            $this->suspendMarket();
            $this->updateGameResult();
            $this->createStatusDesc(StatusDesc::STATUS_FINISHED);
        } catch (\Exception $exception) {
            DB::connection('line')->rollBack();
            throw $exception;
        }

        DB::connection('line')->commit();

        $this->sendMessageFinished();

        return true;
    }

    /**
     * @return int|null
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    protected function sendMessageFinished()
    {
        if(($status = Calculator::sendMessageApprove($this->eventId)) !== 'ok')
        {
            throw new \RuntimeException("Unable to send approve");
        }

        $this->sendAmQpMessage(StatusDesc::STATUS_FINISHED);
    }

    protected function updateGameResult()
    {
        if(!ResultGame::updateApprove($this->eventId)) {
            throw new \RuntimeException("Can't update approve event");
        }
    }

    protected function suspendMarket()
    {
        if(!(new Market())->suspendMarketEvent($this->eventId))
        {
            throw new \RuntimeException("Can't suspend market");
        }
    }

    protected function createStatusDesc(string $statusName)
    {
        if(! StatusDesc::createStatus($statusName, $this->eventId)){
            throw new \RuntimeException("Can't insert status_desc");
        }
    }

    protected function sendAmQpMessage(string $status)
    {
        $data = [
            'type' => $status,
            'data' => ['event_id' => $this->eventId]
        ];

        $response = app('AmqpService')->sendMsg(
            $this->amqpExchange,
            $this->amqpKey. $this->eventId,
            json_encode($data)
        );

        if(!$response){
            throw new \RuntimeException('AmQp send failed');
        }

        return true;
    }
}