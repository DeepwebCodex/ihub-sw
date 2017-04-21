<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\DriveMediaNovomaticDeluxe;

use App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe\Deposit;
use App\Components\Transactions\Strategies\DriveMediaNovomaticDeluxe\Withdrawal;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use Illuminate\Http\Request;

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

        if (!$betAmount && !$winLose) {
            $this->operationId = round(microtime(true) * 1000);
            $this->balance = $user->getBalance();

            return true;
        }

        $transactions = $this->getTransactions($betAmount, $winLose);
        $object_id = 0;

        foreach ($transactions as $key => $item) {

            $transactionRequest = new TransactionRequest(
                $service_id,
                ($item['type'] == "win" ? $object_id : $request->input('gameId')),
                $user->id, $user->getCurrency(),
                ($item['type'] == "bet" ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT),
                $item['amount'],
                $item['type'],
                $request->input('tradeId'),
                $request->input('gameId'),
                $request->get('partnerId'),
                $request->get('cashdeskId'),
                $request->get('userIp')
            );

            $transactionHandler = new TransactionHandler($transactionRequest, $user);

            if($item['type'] == 'bet') {
                $transaction = $transactionHandler->handle(new Withdrawal($request));
                $object_id = $transaction->object_id;
            }

            if($item['type'] == 'win') {
                $transaction = $transactionHandler->handle(new Deposit($request));
            }

            $this->operationId = $transaction->operation_id;
            $this->balance = $transaction->getBalance();
        }

        return $transaction;
    }

    private function getTransactions(float $betAmount, float $winLose, array $transactions = [])
    {
        if (!$betAmount && $winLose) {
            array_push($transactions, [
                'type'      => 'win',
                'amount'    => $winLose
            ]);
        } else if ($betAmount) {
            array_push($transactions, [
                'type'      => 'bet',
                'amount'    => $betAmount
            ]);
            $winAmount = $winLose + $betAmount;
            if ($winAmount) {
                array_push($transactions, [
                    'type' => 'win',
                    'amount' => $winAmount
                ]);
            } else if ((int) $winAmount === 0) {
            } else {
                throw new ApiHttpException(404, null, CodeMapping::getByErrorCode(StatusCode::BAD_CODITION));
            }
        }

        return $transactions;
    }

}
