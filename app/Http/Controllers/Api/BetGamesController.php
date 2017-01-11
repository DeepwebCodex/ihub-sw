<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\BetGamesApiFormatter;
use App\Components\Integrations\BetGames\ApiMethod;
use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\ResponseData;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Integrations\BetGames\TransactionMap;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\BetGames\ProcessBetGames;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\Templates\BetGamesTemplate;
use App\Http\Requests\BetGames\BaseRequest;
use App\Http\Requests\BetGames\BetRequest;
use App\Http\Requests\BetGames\WinRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class BetGamesController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = BetGamesTemplate::class;

    private $userId;

    /**
     * BetGamesController constructor.
     * @param BetGamesApiFormatter $formatter
     */
    public function __construct(BetGamesApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.betGames');

        $this->middleware('input.xml')->except(['error']);

        /**
         * @see BetGamesValidation::checkSignature, BetGamesValidation::checkTime, BetGamesValidation::checkMethod
         */
        Validator::extend('check_signature', 'App\Http\Requests\Validation\BetGamesValidation@checkSignature');
        Validator::extend('check_time', 'App\Http\Requests\Validation\BetGamesValidation@checkTime');
        Validator::extend('check_token', 'App\Http\Requests\Validation\BetGamesValidation@checkToken');
        Validator::extend('check_method', 'App\Http\Requests\Validation\BetGamesValidation@checkMethod');

        $this->userId = app('GameSession')->get('user_id') ?? 0;
    }

    /**
     * @param BaseRequest $request
     * @return mixed
     */
    public function index(BaseRequest $request)
    {
        $apiMethod = new ApiMethod($request->input('method'));
        return app()->call([$this, $apiMethod->get()], $request->all());
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function ping(BaseRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        return $this->responseOk('ping', $request->input('token'));
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function account(BaseRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        $user = IntegrationUser::get($this->userId, $this->getOption('service_id'), 'betGames');
        $attributes = $user->getAttributes();

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'user_id' => $attributes['id'],
            'username' => $attributes['first_name'],
            'currency' => $user->getCurrency(),
            'info' => $attributes['last_name'],
        ]);
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function refreshToken(BaseRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        return $this->responseOk($request->input('method'), $request->input('token'));
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function newToken(BaseRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        $newToken = app('GameSession')->regenerate($request->input('token'));
        return $this->responseOk($request->input('method'), $newToken, [], false);
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function getBalance(BaseRequest $request)
    {
        $user = IntegrationUser::get($this->userId, $this->getOption('service_id'), 'betGames');
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);

        return $this->responseOk('get_balance', $request->input('token'), [
            'balance' => $user->getBalanceInCents()
        ]);
    }

    /**
     * @param BetRequest $request
     * @return Response
     */
    public function bet(BetRequest $request)
    {
        $user = IntegrationUser::get($this->userId, $this->getOption('service_id'), 'betGames');

        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);

        $transactionMap = new TransactionMap($request->input('method'));
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('params.bet_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('params.amount')),
            $transactionMap->getType(),
            $request->input('params.transaction_id'),
            0 // TODO:: filler - get actual game id from partner
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessBetGames::class));

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'balance_after' => $response->getBalanceInCents(),
            'already_processed' => $response->isDuplicate() ? 1 : 0
        ]);
    }

    /**
     * @param WinRequest $request
     * @return Response
     */
    public function win(WinRequest $request)
    {
        $userId = $request->input('params.player_id');
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'betGames');

        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token'), 'balance' => $user->getBalanceInCents()]);

        $transactionMap = new TransactionMap($request->input('method'));
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('params.bet_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('params.amount')),
            $transactionMap->getType(),
            $request->input('params.transaction_id'),
            0 // TODO:: filler - get actual game id from partner
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessBetGames::class));

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'balance_after' => $response->getBalanceInCents(),
            'already_processed' => $response->isDuplicate() ? 1 : 0
        ]);
    }

    /**
     * @param $method
     * @param $token
     * @param array $params
     * @param bool $prolong
     * @return Response
     */
    public function responseOk($method, $token, array $params = [], $prolong = true)
    {
        if($prolong) {
            app('GameSession')->prolong($token);
        }
        $data = new ResponseData($method, $token, $params, CodeMapping::getByErrorCode(StatusCode::OK));
        return $this->respond(Response::HTTP_OK, '', $data->ok());
    }
}