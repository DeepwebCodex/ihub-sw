<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\NetEntertainmentApiFormatter;
use App\Components\Integrations\NetEntertainment\ApiMethod;
use App\Components\Integrations\NetEntertainment\ApiValidation;
use App\Components\Integrations\NetEntertainment\Balance;
use App\Components\Integrations\NetEntertainment\Hmac;
use App\Components\Integrations\NetEntertainment\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\NetEntertainment\ProcessNetEntertainment;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\NetEntertainmentTemplate;
use App\Http\Requests\NetEntertainment\BaseRequest;
use App\Http\Requests\NetEntertainment\BetRequest;
use App\Http\Requests\NetEntertainment\GetBalanceRequest;
use App\Http\Requests\NetEntertainment\WinRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class NetEntertainmentController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = NetEntertainmentTemplate::class;

    private $userId;
    private $partnerId;
    private $cashdeskId;
    private $gameId;

    /**
     * NetEntertainmentController constructor.
     * @param NetEntertainmentApiFormatter $formatter
     */
    public function __construct(NetEntertainmentApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.netEntertainment');

        $this->middleware('check.ip:netEntertainment');
        $this->middleware('input.json')->except(['error']);

        /**
         * @see NetEntertainmentValidation::checkHmac,NetEntertainmentValidation::checkMethod
         */
        Validator::extend('check_hmac', 'App\Http\Requests\Validation\NetEntertainmentValidation@checkHmac');
        Validator::extend('check_method', 'App\Http\Requests\Validation\NetEntertainmentValidation@checkMethod');
    }

    /**
     * @param BaseRequest $request
     * @return mixed
     */
    public function index(BaseRequest $request)
    {
        $apiMethod = new ApiMethod($request->input('type'));
        if(!$apiMethod->isOffline()) {
            $this->userId = app('GameSession')->get('user_id');
            $this->partnerId = app('GameSession')->get('partner_id');
            $this->cashdeskId = app('GameSession')->get('cashdesk_id');
            $this->gameId = app('GameSession')->get('game_id');
        }

        return app()->call([$this, $apiMethod->get()], $request->all());
    }

    public function ping(BaseRequest $request)
    {
        return $this->responseOk();
    }

    public function getBalance(GetBalanceRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEntertainment.service_id');

        $user = IntegrationUser::get($this->userId, $service_id, 'netEntertainment');
        return $this->responseOk([
            'balance' => Balance::toFloat($user->getBalanceInCents())
        ]);
    }

    public function bet(BetRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEntertainment.service_id');
        $user = IntegrationUser::get($this->userId, $service_id, 'netEntertainment');

        (new ApiValidation($request))
            ->checkTransactionParams(
                $service_id,
                TransactionRequest::TRANS_BET,
                $this->partnerId
            )
            ->checkCurrency($user);

        $transactionRequest = new TransactionRequest(
            $service_id,
            $request->input('i_gameid'),
            $user->id,
            $request->input('currency'),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_BET,
            $request->input('tid'),
            $this->gameId,
            $this->partnerId,
            $this->cashdeskId,
            app('GameSession')->get('userIp')
        );
        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessNetEntertainment::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    public function win(WinRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEntertainment.service_id');
        $user = IntegrationUser::get($this->userId, $service_id, 'netEntertainment');

        (new ApiValidation($request))
            ->checkTransactionParams(
                $service_id,
                TransactionRequest::TRANS_WIN,
                $this->partnerId
            )
            ->checkCurrency($user);

        $transactionRequest = new TransactionRequest(
            $service_id,
            $request->input('i_gameid'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            (float)$request->input('amount'),
            TransactionRequest::TRANS_WIN,
            $request->input('tid'),
            $this->gameId,
            $this->partnerId,
            $this->cashdeskId,
            app('GameSession')->get('userIp')
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessNetEntertainment::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    public function roundInfo()
    {
        return $this->responseOk();
    }

    private function responseOk(array $params = [], $prolong = true)
    {
        $view = array_merge([
            'status' => 'OK',
        ], $params);

        $view['hmac'] = (new Hmac($view))->get();

        return $this->respond(Response::HTTP_OK, '', $view);
    }
}