<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\MrSlotty\CodeMapping;
use App\Components\Integrations\MrSlotty\MrSlottyHelper;
use App\Components\Integrations\MrSlotty\StatusCode;
use App\Components\Transactions\Strategies\MrSlotty\ProcessMrSlotty;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\MrSlottyTemplate;
use App\Http\Requests\MrSlotty\BalanceRequest;
use App\Http\Requests\MrSlotty\BetRequest;
use App\Http\Requests\MrSlotty\BetWinRequest;
use App\Http\Requests\MrSlotty\WinRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class MrSlottyController extends BaseApiController
{
    public static $exceptionTemplate = MrSlottyTemplate::class;

    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.mrslotty');

        Validator::extend('check_hash', 'App\Http\Requests\Validation\MrSlottyValidation@checkHash');
    }

    public function index(Request $request)
    {
        $method = MrSlottyHelper::mapMethod($request->input('action'));
        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function balance(BalanceRequest $request)
    {
        $user = IntegrationUser::get($request->input('player_id'), $this->getOption('service_id'), 'MrSlotty');

        return $this->respondOk(200, null, [
            "balance"   => $user->getBalanceInCents(),
            "currency"  => $user->getCurrency()
        ]);
    }

    public function bet(BetRequest $request)
    {
        $user = IntegrationUser::get($request->input('player_id'), $this->getOption('service_id'), 'MrSlotty');

        parse_str($request->input('extra'), $userParams);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('round_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            MrSlottyHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_BET,
            $request->input('transaction_id'),
            $request->input('game_id'),
            $userParams['partner_id'],
            $userParams['cashdesk_id'],
            $userParams['user_ip']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessMrSlotty());

        return $this->respondOk(200, null, [
            "balance" => $transactionResponse->getBalanceInCents(),
            "currency" => $user->getCurrency()
        ]);
    }

    public function win(WinRequest $request)
    {
        $user = IntegrationUser::get($request->input('player_id'), $this->getOption('service_id'), 'MrSlotty');

        parse_str($request->input('extra'), $userParams);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('round_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            MrSlottyHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_WIN,
            $request->input('transaction_id'),
            $request->input('game_id'),
            $userParams['partner_id'],
            $userParams['cashdesk_id'],
            $userParams['user_ip']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessMrSlotty());

        return $this->respondOk(200, null, [
            "balance" => $transactionResponse->getBalanceInCents(),
            "currency" => $user->getCurrency()
        ]);
    }

    public function betWin(BetWinRequest $request)
    {
        $user = IntegrationUser::get($request->input('player_id'), $this->getOption('service_id'), 'MrSlotty');

        parse_str($request->input('extra'), $userParams);

        $transactions = MrSlottyHelper::getTransactions(
            $request->input('amount'),
            $request->input('win'),
            $request->input('bet_transaction_id'),
            $request->input('win_transaction_id')
        );

        foreach ($transactions as $key => $transaction)
        {
            $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'),
                $request->input('round_id'),
                $user->id,
                $user->getCurrency(),
                $transaction['direction'],
                MrSlottyHelper::amountCentsToWhole($transaction['amount']),
                $transaction['type'],
                $transaction['transaction_id'],
                $request->input('game_id'),
                $userParams['partner_id'],
                $userParams['cashdesk_id'],
                $userParams['user_ip']
            );

            $transactionHandler = new TransactionHandler($transactionRequest, $user);

            $transactionResponse = $transactionHandler->handle(new ProcessMrSlotty());

            if($key == 0 && sizeof($transactions) == 2)
            {
                $user->updateBalance($transactionResponse->getBalanceInCents());
            }
        }

        return $this->respondOk(200, null, [
            "balance" => $transactionResponse->getBalanceInCents(),
            "currency" => $user->getCurrency()
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(500, null, CodeMapping::getByMeaning(StatusCode::INTERNAL_SERVER_ERROR));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = null, array $payload = [])
    {
        $payload = array_merge($payload, [
            'status' => $statusCode,
        ]);

        return parent::respondOk($statusCode, '', $payload);
    }

}
