<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

use App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe\Deposit;
use App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe\Withdrawal;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use Illuminate\Http\Request;
use stdClass;

/**
 * Description of Operation
 *
 * @author petroff
 */
class Operation {

    protected $operationId;
    protected $balance;

    public function getOperationId() {
        return $this->operationId;
    }

    public function getBalance() {
        return $this->balance;
    }

    public function process(IntegrationUser $user, Request $request, int $service_id) {

        $betAmount = (float) $request->input('bet');
        $winLose = (float) $request->input('winLose');

        if (!$betAmount && $winLose) {
            return $this->win($user, $request, $service_id, $winLose);
        } else if (!$betAmount && !$winLose) {
            $this->operationId = round(microtime(true) * 1000);
            $this->balance = $user->getBalance();
            return true;
        } else if ($betAmount) {
            $responseBet = $this->bet($user, $request, $service_id, $betAmount);
            $winAmount = $winLose + $betAmount;
            if ($winAmount) {
                return $this->win($user, $request, $service_id, $winAmount, $responseBet->object_id);
            } else if ((int) $winAmount === 0) {
                return $responseBet;
            } else {
                throw new ApiHttpException(404, null, CodeMapping::getByErrorCode(StatusCode::BAD_CODITION));
            }
        }
    }

    private function bet(IntegrationUser $user, Request $request, int $service_id, float $amount) {
        $transactionRequest = new TransactionRequest(
                $service_id, $request->input('gameId'), $user->id, $user->getCurrency(), TransactionRequest::D_WITHDRAWAL, $amount, TransactionRequest::TRANS_BET, $request->input('tradeId'), $request->input('gameId')
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transaction = $transactionHandler->handle(new Withdrawal($request));
        $this->operationId = $transaction->operation_id;
        $this->balance = $transaction->getBalance();
        return $transaction;
    }

    private function win(IntegrationUser $user, Request $request, int $service_id, float $amount, int $object_id = 0) {
        $transactionRequest = new TransactionRequest(
                $service_id, $object_id, $user->id, $user->getCurrency(), TransactionRequest::D_DEPOSIT, $amount, TransactionRequest::TRANS_WIN, $request->input('tradeId'), $request->input('gameId')
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transaction = $transactionHandler->handle(new Deposit($request));
        $this->operationId = $transaction->operation_id;
        $this->balance = $transaction->getBalance();
        return $transaction;
    }

}
