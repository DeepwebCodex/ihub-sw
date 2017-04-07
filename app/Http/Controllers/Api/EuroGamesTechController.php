<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\EgtXmlApiFormatter;
use App\Components\Integrations\EuroGamesTech\CodeMapping;
use App\Components\Integrations\EuroGamesTech\EgtHelper;
use App\Components\Integrations\GameSession\TokenControl\TokenControl;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\EuroGamesTechTemplate;
use App\Http\Requests\EuroGamesTech\AuthRequest;
use App\Http\Requests\EuroGamesTech\DepositRequest;
use App\Http\Requests\EuroGamesTech\PlayerBalanceRequest;
use App\Http\Requests\EuroGamesTech\WithdrawAndDepositRequest;
use App\Http\Requests\EuroGamesTech\WithdrawRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class EuroGamesTechController
 * @package App\Http\Controllers\Api
 */
class EuroGamesTechController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = EuroGamesTechTemplate::class;

    public function __construct(EgtXmlApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.egt');

        $this->middleware('check.ip:egt');
        $this->middleware('input.xml')->except(['error']);
        $this->middleware('input.egt.parsePlayerId')->except(['error']);

        Validator::extend('validate_defence_code', 'App\Http\Requests\Validation\EuroGamesTechValidation@validateDefenceCode');
        Validator::extend('validate_deposit', 'App\Http\Requests\Validation\EuroGamesTechValidation@validateDepositReason');
        Validator::extend('validate_withdraw', 'App\Http\Requests\Validation\EuroGamesTechValidation@validateWithdrawReason');
    }

    public function authenticate(AuthRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        EgtHelper::checkInputCurrency($user->getCurrency(), EgtHelper::getCurrencyFromPortalCode($request->input('PortalCode')));

        return $this->respondOk(200, null, [
            'Balance' => $user->getBalanceInCents()
        ]);
    }

    public function getPlayerBalance(PlayerBalanceRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        EgtHelper::checkInputCurrency($user->getCurrency(), $request->input('Currency'));

        return $this->respondOk(200, null, [
            'Balance' => $user->getBalanceInCents()
        ]);
    }

    public function withdraw(WithdrawRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        EgtHelper::checkInputCurrency($user->getCurrency(), $request->input('Currency'));

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('GameNumber'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('Amount')),
            EgtHelper::getTransactionType($request->input('Reason')),
            $request->input('TransferId'),
            $request->input('GameId'),
            $request->input('PartnerId'),
            $request->input('CashdeskId'),
            $request->input('UserIp')
        );

        $transactionResponse = EgtHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'Balance' => $transactionResponse->getBalanceInCents(),
            'CasinoTransferId' => $transactionResponse->operation_id
        ]);
    }

    public function deposit(DepositRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        EgtHelper::checkInputCurrency($user->getCurrency(), $request->input('Currency'));

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('GameNumber'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('Amount')),
            EgtHelper::getTransactionType($request->input('Reason'), true),
            $request->input('TransferId'),
            $request->input('GameId'),
            $request->input('PartnerId'),
            $request->input('CashdeskId'),
            $request->input('UserIp')
        );

        $transactionResponse = EgtHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'Balance' => $transactionResponse->getBalanceInCents(),
            'CasinoTransferId' => $transactionResponse->operation_id
        ]);
    }

    public function withdrawAndDeposit(WithdrawAndDepositRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'egt');

        EgtHelper::checkInputCurrency($user->getCurrency(), $request->input('Currency'));

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('GameNumber'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('Amount')),
            TransactionRequest::TRANS_BET,
            $request->input('TransferId'),
            $request->input('GameId'),
            $request->input('PartnerId'),
            $request->input('CashdeskId'),
            $request->input('UserIp')
        );

        $transactionResponse = EgtHelper::handleTransaction($transactionRequest, $user);

        $user->updateBalance($transactionResponse->getBalanceInCents());

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('GameNumber'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('WinAmount')),
            EgtHelper::getTransactionType($request->input('Reason')),
            $request->input('TransferId'),
            $request->input('GameId'),
            $request->input('PartnerId'),
            $request->input('CashdeskId'),
            $request->input('UserIp')
        );

        $transactionResponse = EgtHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'Balance' => $transactionResponse->getBalanceInCents(),
            'CasinoTransferId' => $transactionResponse->operation_id
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = null, array $payload = [])
    {
        list($message, $code) = array_values(CodeMapping::getByMeaning(CodeMapping::SUCCESS));

        $payload = array_merge($payload, [
            'ErrorCode' => $code,
            'ErrorMessage' => $message
        ]);

        return parent::respondOk($statusCode, '', $payload);
    }
}
