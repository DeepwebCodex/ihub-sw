<?php

namespace App\Components\Integrations\VirtualSports;

use App\Components\Integrations\VirtualSports\Interfaces\DataMapperInterface;
use App\Components\Integrations\VirtualSports\Interfaces\EventBuilderInterface;
use App\Components\Integrations\VirtualSports\Interfaces\EventResultInterface;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Models\Line\Event;
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

    protected $transactionConnections = [
        'line',
        'trans'
    ];

    public function __construct(int $eventId = null)
    {
        $this->eventId = $eventId;
    }

    public function createOnlyTournamentAndCategory(DataMapperInterface $dataMapper) : bool
    {
        /**@var EventBuilderInterface $eventBuilder*/
        $eventBuilder = new $this->eventBuilderClass($dataMapper);

        $this->startDbTransaction();

        try
        {
            //need a category for event
            $eventCategory = $eventBuilder->createCategory();

            //creating tournament for category
            $eventBuilder->createTournament($eventCategory->id, $dataMapper->getTournamentName());

        } catch (\Exception $exception) {
            $this->rollBackDbTransaction();

            throw $exception;
        }
        $this->commitDbTransaction();


        return true;
    }

    public function create(DataMapperInterface $dataMapper) : bool
    {
        /**@var EventBuilderInterface $eventBuilder*/
        $eventBuilder = new $this->eventBuilderClass($dataMapper);

        $this->startDbTransaction();

        try
        {
            $this->eventId = $eventBuilder->create();
        } catch (\Exception $exception) {
            $this->rollBackDbTransaction();

            throw $exception;
        }
        $this->commitDbTransaction();


        return true;
    }

    public function setResult(DataMapperInterface $dataMapper, bool $finish = true)
    {
        DB::connection('line')->beginTransaction();
        try {

            if($finish) {
                if (!ResultGame::isResultsApproved($this->eventId)) {
                    $this->sendMessageFinished();
                    return;
                }
            }

            /**@var EventResultInterface $eventResult*/
            $eventResult = new $this->eventResultClass($dataMapper, $this->eventId);

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

        $event = Event::findById($this->eventId);

        if($event && $event->status_type == 'finished') {
            throw new ApiHttpException(200, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_VOID_FINISHED));
        }

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
                throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CALCULATE_BET));
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
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CALCULATE_BET));
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

    public function addDbTransactionConnection(string $connectionName)
    {
        $this->transactionConnections[] = $connectionName;

        return $this;
    }

    protected function startDbTransaction()
    {
        foreach ($this->transactionConnections as $connection) {
            DB::connection($connection)->beginTransaction();
        }
    }

    protected function commitDbTransaction()
    {
        foreach ($this->transactionConnections as $connection) {
            DB::connection($connection)->commit();
        }
    }

    protected function rollBackDbTransaction()
    {
        foreach ($this->transactionConnections as $connection) {
            DB::connection($connection)->rollBack();
        }
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
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CALCULATE_BET));
        }

        $this->sendAmQpMessage(StatusDesc::STATUS_FINISHED);
    }

    protected function updateGameResult()
    {
        if(!ResultGame::updateApprove($this->eventId)) {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_UPDATE_EVENT_STATUS));
        }
    }

    protected function suspendMarket()
    {
        if(!(new Market())->suspendMarketEvent($this->eventId))
        {
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_UPDATE_MARKET));
        }
    }

    protected function createStatusDesc(string $statusName)
    {
        if(! StatusDesc::createStatus($statusName, $this->eventId)){
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_UPDATE_EVENT_STATUS));
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
            $data
        );

        if(!$response){
            throw new ApiHttpException(500, null, CodeMappingVirtualSports::getByMeaning(CodeMappingVirtualSports::CANT_CALCULATE_BET));
        }

        return true;
    }
}