<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\BetGamesApiFormatter;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\Token;
use App\Components\Integrations\BetGames\TransactionMap;
use App\Components\Integrations\BetGames\TransactionDecorator;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\BetGames\ProcessBetGames;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\BetGamesTemplate;
use App\Http\Requests\BetGames\PingRequest;
use App\Http\Requests\BetGames\GetAccountDetailsRequest;
use App\Http\Requests\BetGames\RefreshTokenRequest;
use App\Http\Requests\BetGames\NewTokenRequest;
use App\Http\Requests\BetGames\GetBalanceRequest;
use App\Http\Requests\BetGames\BetRequest;
use App\Http\Requests\BetGames\WinRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class BetGamesController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = BetGamesTemplate::class;

    public function __construct(BetGamesApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.betGames');

        $this->middleware('input.xml')->except(['error']);

        /**
         * @see BetGamesValidation::checkSignature, BetGamesValidation::checkTime, BetGamesValidation::checkToken
         */
        Validator::extend('check_signature', 'App\Http\Requests\Validation\BetGamesValidation@checkSignature');
        Validator::extend('check_time', 'App\Http\Requests\Validation\BetGamesValidation@checkTime');
        Validator::extend('checkToken', 'App\Http\Requests\Validation\BetGamesValidation@checkToken');
//        Validator::extend('check_expiration_time', 'App\Http\Requests\Validation\EuroGamesTechValidation@checkExpirationTime');
//        Validator::extend('validate_deposit', 'App\Http\Requests\Validation\EuroGamesTechValidation@validateDepositReason');
//        Validator::extend('validate_withdraw', 'App\Http\Requests\Validation\EuroGamesTechValidation@validateWithdrawReason');
    }

    public function ping(PingRequest $request)
    {
        return $this->responseOk('ping',  $request->input('token'));
    }

    public function account(GetAccountDetailsRequest $request)
    {
        $token = new Token($request->input('token'));
//        $user = IntegrationUser::get($token->getUserId(), $this->getOption('service_id'), 'bet_games');
//        $attributes = $user->getAttributes();
        $attributes = ['id' => 11, 'name' => 'test user', 'currency' => 'EUR'];

        return $this->responseOk($request->input('method'), $request->input('token'), [
                'user_id' => $attributes['id'],
                'username' => $attributes['name'],
                'currency' => $attributes['currency'],
                'info' => '',
            ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $token = new Token($request->input('token'));
        $token->refresh();
        return $this->responseOk($request->input('method'), $token->get(),['new_token_value' => $token->getTime()]);
    }

    public function newToken(NewTokenRequest $request)
    {
        $token = new Token($request->input('token'));
        $newToken = $token->setNew();
        return $this->responseOk($request->input('method'), $newToken->get());
    }

    public function getBalance(GetBalanceRequest $request)
    {
        $token = new Token($request->input('token'));

        $user = IntegrationUser::get($token->getUserId(), $this->getOption('service_id'), 'bet_games');

        return $this->responseOk('get_balance', $request->input('token'), [
            'balance' => $user->getBalanceInCents()
        ]);
    }

    public function bet(BetRequest $request)
    {
        $token = new Token($request->input('token'));
        $user = IntegrationUser::get($token->getUserId(), $this->getOption('service_id'), 'bet_games');

        $transactionMap = new TransactionMap($request->input('method'));
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('params.bet_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('params.amount')),
            $transactionMap->getType()
        );

        $decorator = new  TransactionDecorator(new  TransactionHandler($transactionRequest, $user));
        $response = $decorator->handle(new ProcessBetGames());

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'balance_after' => $response->getBalanceInCents(),
            'already_processed' => 0
        ]);
    }

    public function win(WinRequest $request)
    {
        $user = IntegrationUser::get($request->input('PlayerId'), $this->getOption('service_id'), 'bet_games');

        $transactionMap = new TransactionMap($request->input('method'));
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('params.bet_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('params.amount')),
            $transactionMap->getType()
        );

        $decorator = new  TransactionDecorator(new  TransactionHandler($transactionRequest, $user));
        $response = $decorator->handle(new ProcessBetGames());

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'balance_after' => $response->getBalanceInCents(),
            'already_processed' => 0
        ]);
    }


    public function error()
    {
        throw new ApiHttpException(404, null);
    }
    /**
     * @param $method
     * @param $token
     * @param array $params
     * @return Response
     */
    public function responseOk($method, $token, array $params = [])
    {
        $data = [
            'method' => $method,
            'token' => $token,
            'success' => 1,
            'error_code' => 0,
            'error_text' => '',
            'time' => time(),
            'params' => $params
        ];
        $signature = new Signature($data);

        return $this->respond(Response::HTTP_OK, '', array_merge($data, [
            'signature' => $signature->getHash(),
        ]));
    }
}