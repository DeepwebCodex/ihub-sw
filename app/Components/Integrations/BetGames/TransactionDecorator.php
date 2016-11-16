<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionResponse;
use App\Exceptions\Api\ApiHttpException;

class TransactionDecorator
{

    /**
     * @var TransactionHandler
     */
    private $handler;

    /**
     * TransactionDecorator constructor.
     * @param TransactionHandler $handler
     */
    public function __construct(TransactionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param TransactionProcessorInterface $strategy
     * @return TransactionResponse
     */
    public function handle(TransactionProcessorInterface $strategy)
    {
        $response = $this->handler->handle($strategy);

        if ($response->isDuplicate()) {
            throw new ApiHttpException(409, null, array_merge(CodeMapping::getByMeaning(CodeMapping::DUPLICATE), [
                'Balance' => $response->getBalance() * 100,
                'CasinoTransferId' => $response->operation_id
            ]));
        }

        if ($response->operation_id === null) {
            throw new ApiHttpException(504, null, CodeMapping::getByMeaning(CodeMapping::TIMED_OUT));
        }

        return $response;
    }
}