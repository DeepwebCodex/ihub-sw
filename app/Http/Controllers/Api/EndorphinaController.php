<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\EndorphinaApiFormatter;
use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Transactions\Strategies\Endorphina\Deposit;
use App\Components\Transactions\Strategies\Endorphina\Refund;
use App\Components\Transactions\Strategies\Endorphina\Withdrawal;
use App\Exceptions\Api\Templates\EndorphinaTemplate;
use App\Http\Requests\Endorphina\BalanceRequest;
use App\Http\Requests\Endorphina\BaseRequest;
use App\Http\Requests\Endorphina\BetRequest;
use App\Http\Requests\Endorphina\RefundRequest;
use App\Http\Requests\Endorphina\WinRequest;
use App\Http\Requests\Validation\EndorphinaValidation;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHelper;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use function app;
use function config;
use Exception;

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
        $user = IntegrationUser::get((int)app('GameSession')->get('user_id'), $service_id, 'endorphina');
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
            'player' => (string)$user->id,
            'currency' => $user->getCurrency(),
            'game' => app('GameSession')->get('game_id')
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
            $this->getOption('service_id'),
            TransactionRequest::PROCEDURAL_OBJECT_ID,
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_BET,
            $request->input('id'),
            app('GameSession')->get('game_id'),
            app('GameSession')->get('partner_id'),
            app('GameSession')->get('cashdesk_id'),
            app('GameSession')->get('userIp')
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Withdrawal());
        return $this->respondOk(Response::HTTP_OK, '', [
            'balance' => $response->getBalanceInCents(),
            'transactionId' => (string)$response->operation_id
        ]);
    }

    //This operation can be offline
    public function win(WinRequest $request, int $partnerIdRouter = null, int $cashdeskIdRouter = null)
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get((int) $request->input('player'), $service_id, 'endorphina');
        
        //for Backward compatibility
        $partnerId = $partnerIdRouter ?? app('GameSession')->get('partner_id');
        $cashdeskId = $cashdeskIdRouter ?? app('GameSession')->get('cashdesk_id');
        $currency = $request->input('currency') ?? $user->getCurrency();
        $gameId = $request->input('game') ?? app('GameSession')->get('game_id');

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            TransactionRequest::PROCEDURAL_OBJECT_ID,
            $user->id,
            $currency,
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_WIN,
            $request->input('id'),
            $gameId,
            $partnerId,
            $cashdeskId,
            $this->getIp()
        );
        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Deposit());
        return $this->respondOk(Response::HTTP_OK, '', [
            'balance' => $response->getBalanceInCents(),
            'transactionId' => (string)$response->operation_id
        ]);
    }

    //This operation can be offline
    public function refund(RefundRequest $request, int $partnerIdRouter = null, int $cashdeskIdRouter = null)
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get((int) $request->input('player'), $service_id, 'endorphina');
        
        //for Backward compatibility
        $partnerId = $partnerIdRouter ?? app('GameSession')->get('partner_id');
        $cashdeskId = $cashdeskIdRouter ?? app('GameSession')->get('cashdesk_id');
        $currency = $request->input('currency') ?? $user->getCurrency();
        $gameId = $request->input('game') ?? app('GameSession')->get('game_id');
        
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'), 
            TransactionRequest::PROCEDURAL_OBJECT_ID, 
            $user->id, 
            $currency,
            TransactionRequest::D_DEPOSIT, 
            TransactionHelper::amountCentsToWhole($request->input('amount')), 
            TransactionRequest::TRANS_REFUND, 
            $request->input('id'), 
            $gameId,
            $partnerId,
            $cashdeskId,
            $this->getIp()
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new Refund());
        return $this->respondOk(Response::HTTP_OK, '', [
            'balance' => $response->getBalanceInCents(),
            'transactionId' => (string)$response->operation_id
        ]);
    }
    
    private function getIp(): string
    {
        try {
            return app('GameSession')->get('userIp');
        } catch (Exception $ex) {
            return Request::ip();
        }
    }
}
