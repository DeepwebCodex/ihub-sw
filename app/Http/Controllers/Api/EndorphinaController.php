<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\EndorphinaApiFormatter;
use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\Game;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\Endorphina\Deposit;
use App\Components\Transactions\Strategies\Endorphina\Refund;
use App\Components\Transactions\Strategies\Endorphina\Withdrawal;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\EndorphinaTemplate;
use App\Http\Requests\Endorphina\BalanceRequest;
use App\Http\Requests\Endorphina\BaseRequest;
use App\Http\Requests\Endorphina\BetRequest;
use App\Http\Requests\Endorphina\RefundRequest;
use App\Http\Requests\Endorphina\WinRequest;
use App\Http\Requests\Validation\EndorphinaValidation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use function app;
use function config;

class EndorphinaController extends BaseApiController
{

    use MetaDataTrait;

    public static $exceptionTemplate = EndorphinaTemplate::class;

    public function __construct(EndorphinaApiFormatter $formatter)
    {
        parent::__construct($formatter);
        $this->options = config('integrations.endorphina');

        $this->middleware('check.ip:endorphina');
        Validator::extend('check_sign', 'App\Http\Requests\Validation\EndorphinaValidation@checkSign');
    }

    private function prepareUser(): IntegrationUser
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get((int) app('GameSession')->get('user_id'), $service_id, 'endorphina');
        EndorphinaValidation::checkCurrency($user->getCurrency(), app('GameSession')->get('currency'));
        return $user;
    }

    public function error()
    {
        throw new ApiHttpException(500, 'Unknown method', CodeMapping::getByErrorCode(StatusCode::UNKNOWN_METHOD));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        return parent::respondOk($statusCode, $message, $payload);
    }

    public function session(BaseRequest $request)
    {
        $user = $this->prepareUser();
        return $this->respondOk(Response::HTTP_OK, '', [
                    'player' => $user->id,
                    'currency' => $user->getCurrency(),
                    'game' => Game::getGame((int) app('GameSession')->get('game_id'))
        ]);
    }

    public function balance(BalanceRequest $request)
    {
        $user = $this->prepareUser();
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $user->getBalanceInCents(),
        ]);
    }

    public function bet(BetRequest $request)
    {
        $user = $this->prepareUser();
        $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'), 0, $user->id, $user->getCurrency(), TransactionRequest::D_WITHDRAWAL, TransactionHelper::amountCentsToWhole($request->input('amount')), TransactionRequest::TRANS_BET, $request->input('id'), $request->input('game'), app('GameSession')->get('partner_id'), app('GameSession')->get('cashdesk_id'), app('GameSession')->get('userIp')
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Withdrawal());
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $response->getBalanceInCents(),
                    'transactionId' => $response->operation_id
        ]);
    }

    public function win(WinRequest $request)
    {
        $user = $this->prepareUser();
        $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'), 0, $user->id, $user->getCurrency(), TransactionRequest::D_DEPOSIT, TransactionHelper::amountCentsToWhole($request->input('amount')), TransactionRequest::TRANS_WIN, $request->input('id'), $request->input('game'), app('GameSession')->get('partner_id'), app('GameSession')->get('cashdesk_id'), app('GameSession')->get('userIp')
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Deposit());
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $response->getBalanceInCents(),
                    'transactionId' => $response->operation_id
        ]);
    }

    public function refund(RefundRequest $request)
    {
        $user = $this->prepareUser();
        $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'), 0, $user->id, $user->getCurrency(), TransactionRequest::D_DEPOSIT, TransactionHelper::amountCentsToWhole($request->input('amount')), TransactionRequest::TRANS_REFUND, $request->input('id'), $request->input('game'), app('GameSession')->get('partner_id'), app('GameSession')->get('cashdesk_id'), app('GameSession')->get('userIp')
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Refund());
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $response->getBalanceInCents(),
                    'transactionId' => $response->operation_id
        ]);
    }

}
