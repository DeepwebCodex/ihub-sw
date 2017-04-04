<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\BetGamesApiFormatter;
use App\Components\Integrations\BetGames\ApiMethod;
use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Integrations\BetGames\TransactionMap;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\BetGames\ProcessBetGames;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\BetGamesTemplate;
use App\Http\Requests\BetGames\BaseRequest;
use App\Http\Requests\BetGames\BetRequest;
use App\Http\Requests\BetGames\OnlineRequest;
use App\Http\Requests\BetGames\WinRequest;
use App\Models\Transactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class BetGamesController
 *
 * @package App\Http\Controllers\Api
 */
class BetGamesController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = BetGamesTemplate::class;

    private $userId;
    private $partnerId;
    private $cashdeskId;
    private $gameId;
    private $userIP;

    /**
     * BetGamesController constructor.
     * @param BetGamesApiFormatter $formatter
     */
    public function __construct(BetGamesApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.betGames');

        $this->middleware('check.ip:betGames');
        $this->middleware('input.xml')->except(['error']);
        $this->middleware('input.bg.parsePlayerIdOnWin');

        /**
         * @see BetGamesValidation::checkTime, BetGamesValidation::checkMethod
         */
        Validator::extend('check_time', 'App\Http\Requests\Validation\BetGamesValidation@checkTime');
        Validator::extend('check_token', 'App\Http\Requests\Validation\BetGamesValidation@checkToken');
        Validator::extend('check_method', 'App\Http\Requests\Validation\BetGamesValidation@checkMethod');
    }

    /**
     * @param BaseRequest $request
     * @return mixed
     */
    public function index(BaseRequest $request)
    {
        $apiMethod = new ApiMethod($request->input('method'));
        if (!$apiMethod->isOffline()) {
            $this->userId = app('GameSession')->get('user_id') ?? 0;
            $this->partnerId = app('GameSession')->get('partner_id');
            $this->cashdeskId = app('GameSession')->get('cashdesk_id');
            $this->gameId = app('GameSession')->get('game_id'); // Т.к. у BetGames нет идентификатора игры при запуске, мы из сессии будем получать 0
            $this->userIP = app('GameSession')->get('userIp');

            $this->checkSignature($request);

            $this->addMetaField('partnerId', $this->partnerId);
            $this->addMetaField('cashdeskId', $this->cashdeskId);
        }

        return app()->call([$this, $apiMethod->get()], $request->all());
    }

    private function checkSignature(BaseRequest $request):bool
    {
        $all = $request->all();
        unset($all['signature']);

        $signature = new Signature($all, $this->partnerId, $this->cashdeskId);
        if ($signature->isWrong($request->input('signature'))) {
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::SIGNATURE,
                'method' => $request->input('method'),
                'token' => $request->input('token'),
            ]);
        }

        return true;
    }

    /**
     * @param BaseRequest $request
     * @return Response
     */
    public function ping(BaseRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        return $this->responseOk('ping', $request->input('token'), [], false);
    }

    /**
     * @param OnlineRequest $request
     * @return Response
     */
    public function account(OnlineRequest $request)
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
     * @param OnlineRequest $request
     * @return Response
     */
    public function refreshToken(OnlineRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        return $this->responseOk($request->input('method'), $request->input('token'));
    }

    /**
     * @param OnlineRequest $request
     * @return Response
     */
    public function newToken(OnlineRequest $request)
    {
        $this->setMetaData(['method' => $request->input('method'), 'token' => $request->input('token')]);
        $newToken = app('GameSession')->regenerate($request->input('token'), 'md5');
        return $this->responseOk($request->input('method'), $request->input('token'), ['new_token' => $newToken], false);
    }

    /**
     * @param OnlineRequest $request
     * @return Response
     */
    public function getBalance(OnlineRequest $request)
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
            str_slug(transliterate($request->input('params.game'))),
            $this->partnerId,
            $this->cashdeskId,
            $this->userIP
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
        $objectId = $request->input('params.bet_id');

        $betTransaction = Transactions::getBetTransaction(
            $this->getOption('service_id'),
            $userId,
            $objectId
        );

        // Убер костыль для допроводки выигрышей, ставки по которым были сделаны в ферапонте
        if (is_null($betTransaction)) {
            $betTransactions = null;

            try {
                $betTransactions = app('AccountManager')->getOperations($userId, 1, $objectId, $this->getOption('service_id'));
            } catch (\Exception $e) {
            }

            if (is_array($betTransactions)) {
                $betTransaction = new \stdClass();
                $betTransaction->game_id = 'from-ferapont';
                $betTransaction->partner_id = ($betTransactions[0]['partner_id'] != 'null') ? $betTransactions[0]['partner_id'] : ($betTransactions[0]['cashdesk'] == -5 ? 1 : 18);
                $betTransaction->cashdesk = $betTransactions[0]['cashdesk'];
                $betTransaction->client_ip = $betTransactions[0]['client_ip'];
            }
        }
        // Конец убер костыля

        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'betGames');

        $this->setMetaData([
            'method' => $request->input('method'),
            'token' => $request->input('token'),
            'balance' => $user->getBalanceInCents(),
            'partnerId' => !is_null($betTransaction) ? $betTransaction->partner_id : 0,
            'cashdeskId' => !is_null($betTransaction) ? $betTransaction->cashdesk : 0,
        ]);

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
            !is_null($betTransaction) ? $betTransaction->game_id : 0,
            !is_null($betTransaction) ? $betTransaction->partner_id : 0,
            !is_null($betTransaction) ? $betTransaction->cashdesk : 0,
            !is_null($betTransaction) ? $betTransaction->client_ip : ''
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessBetGames::class));

        return $this->responseOk($request->input('method'), $request->input('token'), [
            'balance_after' => $response->getBalanceInCents(),
            'already_processed' => $response->isDuplicate() ? 1 : 0
        ], false);
    }

    /**
     * @param       $method
     * @param       $token
     * @param array $params
     *
     * @return array
     */
    public function prepareResponse(string $method, string $token, array $params)
    {
        foreach ($params as $key => $param) {
            $params[$key] = transliterate(str_slug($param, '_'));
        }

        $error = CodeMapping::getByErrorCode(StatusCode::OK);
        $view = [
            'method' => $method,
            'token' => $token,
            'success' => 1,
            'error_code' => (int)$error['code'],
            'error_text' => (string)$error['message'],
            'time' => time(),
            'params' => $params
        ];
        $view['signature'] = (new Signature($view, $this->partnerId, $this->cashdeskId))->getHash();

        return $view;
    }


    /**
     * @param $method
     * @param $token
     * @param array $params
     * @param bool $prolong
     * @return Response
     */
    private function responseOk(string $method, string $token, array $params = [], $prolong = true)
    {
        if($prolong) {
            app('GameSession')->prolong($token);
        }

        $view = $this->prepareResponse($method, $token, $params);

        return $this->respond(Response::HTTP_OK, '', $view);
    }
}