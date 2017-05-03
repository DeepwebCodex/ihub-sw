<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\FundistApiFormatter;
use App\Components\Integrations\Fundist\ApiMethod;
use App\Components\Integrations\Fundist\ApiValidation;
use App\Components\Integrations\Fundist\Balance;
use App\Components\Integrations\Fundist\Hmac;
use App\Components\Integrations\Fundist\StatusCode;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\Fundist\ProcessFundist;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\FundistTemplate;
use App\Http\Requests\Fundist\BaseRequest;
use App\Http\Requests\Fundist\BetRequest;
use App\Http\Requests\Fundist\GetBalanceRequest;
use App\Http\Requests\Fundist\WinRequest;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

abstract class FundistController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = FundistTemplate::class;

    protected $integration;
    private $userId;
    private $partnerId;
    private $cashdeskId;
    private $gameId;

    /**
     * FundistController constructor.
     * @param FundistApiFormatter $formatter
     */
    public function __construct(FundistApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->integration = $this->getIntegration();
        $this->options = config('integrations.' . $this->integration);

        $this->middleware('input.json');
        $this->middleware('check.ip:' . $this->integration);
        $this->middleware('input.fundist.parsePlayerIdOnOffline');

        Hmac::$INTEGRATION = $this->integration;

        /**
         * @see FundistValidation::checkHmac,FundistValidation::checkMethod
         */
        Validator::extend('check_hmac', 'App\Http\Requests\Validation\FundistValidation@checkHmac');
        Validator::extend('check_method', 'App\Http\Requests\Validation\FundistValidation@checkMethod');
    }

    abstract protected function getIntegration();

    /**
     * @param BaseRequest $request
     * @return mixed
     */
    public function index(BaseRequest $request)
    {
        $apiMethod = new ApiMethod($request->input('type'));
        if (!$apiMethod->isOffline()) {
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
        $service_id = $this->getOption('service_id');

        $user = IntegrationUser::get($this->userId, $service_id, $this->integration);
        return $this->responseOk([
            'balance' => Balance::toFloat($user->getBalanceInCents())
        ]);
    }

    public function bet(BetRequest $request)
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get($this->userId, $service_id, $this->integration);

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
        $response = $transaction->handle(app(ProcessFundist::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    public function win(WinRequest $request)
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get($request->input('userId'), $service_id, $this->integration);

        $betTransaction = Transactions::getBetTransaction(
            $this->getOption('service_id'),
            $user->id,
            $request->input('i_gameid')
        );
        if (is_null($betTransaction)) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::BAD_OPERATION_ORDER,
            ]);
        }

        if ($betTransaction->user_id != $user->id
            || $betTransaction->currency != $request->input('currency')
        ) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::TRANSACTION_MISMATCH,
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
            $betTransaction->game_id,
            $betTransaction->partner_id,
            $betTransaction->cashdesk,
            $betTransaction->client_ip
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessFundist::class));

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

        $view['hmac'] = (new Hmac($view, $this->integration))->get();

        return $this->respond(Response::HTTP_OK, '', $view);
    }
}