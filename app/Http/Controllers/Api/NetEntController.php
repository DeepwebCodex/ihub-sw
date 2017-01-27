<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\NetEnt\ApiMethod;
use App\Components\Integrations\NetEnt\Balance;
use App\Components\Integrations\NetEnt\Hmac;
use App\Components\Integrations\NetEnt\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\NetEnt\ProcessNetEnt;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\NetEntTemplate;
use App\Http\Requests\NetEnt\BaseRequest;
use App\Http\Requests\NetEnt\BetRequest;
use App\Http\Requests\NetEnt\GetBalanceRequest;
use App\Http\Requests\NetEnt\WinRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class NetEntController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = NetEntTemplate::class;

    private $userId;

    /**
     * NetEntController constructor.
     * @param JsonApiFormatter $formatter
     */
    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.netent');

        $this->middleware('input.json')->except(['error']);

        /**
         * @see NetEntValidation::checkHmac,NetEntValidation::checkMethod
         */
        Validator::extend('check_hmac', 'App\Http\Requests\Validation\NetEntValidation@checkHmac');
        Validator::extend('check_method', 'App\Http\Requests\Validation\NetEntValidation@checkMethod');
    }

    /**
     * @param BaseRequest $request
     * @return mixed
     */
    public function index(BaseRequest $request)
    {
        $apiMethod = new ApiMethod($request->input('type'));
        $this->userId = app('GameSession')->get('user_id') ?? 0;

        return app()->call([$this, $apiMethod->get()], $request->all());
    }

    public function ping(BaseRequest $request)
    {
        return $this->responseOk();
    }

    public function getBalance(GetBalanceRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEnt.service_id');

        $user = IntegrationUser::get($this->userId, $service_id, 'netEnt');
        return $this->responseOk([
            'balance' => $user->getBalance()
        ]);
    }

    public function bet(BetRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEnt.service_id');
        $user = IntegrationUser::get($this->userId, $service_id, 'netEnt');

        if($user->getCurrency() != $request->input('currency')){
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::CURRENCY,
            ]);
        }

        $transactionRequest = new TransactionRequest(
            $service_id,
            $request->input('i_gameid'),
            $user->id,
            $request->input('currency'),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_BET,
            $request->input('tid'),
            0 // TODO:: filler - get actual game id from partner
        );
        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessNetEnt::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    public function win(WinRequest $request)
    {
        $service_id = $this->getOption('service_id') ?? config('integrations.netEnt.service_id');
        $user = IntegrationUser::get($this->userId, $service_id, 'netEnt');

        if($user->getCurrency() != $request->input('currency')){
            throw new ApiHttpException(400, null, [
                'code' => StatusCode::CURRENCY,
            ]);
        }

        $transactionRequest = new TransactionRequest(
            $service_id,
            $request->input('i_gameid'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            (float)$request->input('amount'),
            TransactionRequest::TRANS_WIN,
            $request->input('tid'),
            0 // TODO:: filler - get actual game id from partner
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessNetEnt::class));

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