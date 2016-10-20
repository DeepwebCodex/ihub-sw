<?php

namespace App\Http\Controllers\Api;

use App\Components\ExternalServices\Facades\RemoteSession;
use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\Casino\ProcessCasino;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Requests\Casino\AuthRequest;
use App\Http\Requests\Casino\PayInRequest;
use App\Http\Requests\Casino\PayOutRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response as ResponseFacade;

/**
 * Class CasinoController
 * @package App\Http\Controllers\Api
 */
class CasinoController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = CasinoTemplate::class;

    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.casino');

        $this->middleware('input.json')->except(['genToken', 'error']);

        Validator::extend('check_signature', 'App\Http\Requests\Validation\CasinoValidation@CheckSignature');
        Validator::extend('check_time', 'App\Http\Requests\Validation\CasinoValidation@CheckTime');
        Validator::extend('check_amount', 'App\Http\Requests\Validation\CasinoValidation@CheckAmount');
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');
        $user->storeSessionCurrency($user->getCurrency());

        return $this->respondOk(200, 'success', [
            'user_id'   => $user->id,
            'user_name' => $user->login,
            'currency'  => $user->getCurrency(),
            'balance'   => $user->getBalanceInCents()
        ]);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');
        $user->checkSessionCurrency();

        return $this->respondOk(200, 'success', [
           'balance' => $user->getBalanceInCents()
        ]);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\Response
     */
    public function refreshToken(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');
        $user->checkSessionCurrency();

        return $this->respondOk();
    }

    /**
     * @param PayInRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payIn(PayInRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');
        $user->checkSessionCurrency();

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('object_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_BET,
            $request->input('transaction_id')
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessCasino());

        return $this->respondOk(200, 'success', [
            'balance'           => $transactionResponse->getBalanceInCents(),
            'transaction_id'    => $transactionResponse->operation_id
        ]);
    }

    /**
     * @param PayOutRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payOut(PayOutRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');
        $user->checkSessionCurrency();

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('object_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            $request->input('type_operation') === 'rollback'
                ? TransactionRequest::TRANS_REFUND : TransactionRequest::TRANS_WIN,
            $request->input('transaction_id')
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessCasino());

        return $this->respondOk(200, 'success', [
            'balance'           => $transactionResponse->getBalanceInCents(),
            'transaction_id'    => $transactionResponse->operation_id
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @internal param string $casino
     */
    public function genToken(Request $request)
    {
        $casino = $request->input('casino');

        $token = $request->cookie('PHPSESSID');

        $userId = RemoteSession::start($token)->get('user_id');

        if ($userId) {
            $payload = [
                'status' => true,
                'token' => $token,
                'message' => ''
            ];
        } else {
            $payload = [
                'status' => false,
                'token' => '',
                'message' => 'auth failed'
            ];
        }

        /**@var Response $response*/
        $response = ResponseFacade::make(json_encode($payload), 200, [
            'Content-type' => 'application/json'
        ])->withHeaders([
            'Access-Control-Allow-Origin' => $casino ? '*' : 'https://casino.favbet.ro'
        ]);

        return $response;
    }

    public function error()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        $payload = array_merge([
            'status' => true,
            'code' => 1,
            'message' => 'success',
            'time' => time()
        ], $payload);

        $payload = array_merge($payload, ['signature' => CasinoHelper::generateActionSignature($payload)]);

        return parent::respondOk($statusCode, $message, $payload);
    }
}
