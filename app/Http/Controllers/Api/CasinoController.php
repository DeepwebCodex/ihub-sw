<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\Casino\ProcessCasino;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\Exceptions\UserCurrencyException;
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
     * @return \Illuminate\Http\Response
     */
    public function auth(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');

        $this->checkSessionCurrency($user->getCurrency());

        return $this->respondOk(200, 'success', [
            'user_id'   => $user->id,
            'user_name' => $user->login,
            'currency'  => $user->getCurrency(),
            'balance'   => $user->getBalanceInCents()
        ]);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getBalance(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');

        $this->checkSessionCurrency($user->getCurrency());

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

        $this->checkSessionCurrency($user->getCurrency());

        $newToken = app('GameSession')->regenerate($request->input('token'));

        return $this->respondOk(200, 'success', [
            'token' => $newToken
        ]);
    }

    /**
     * @param PayInRequest $request
     * @return \Illuminate\Http\Response
     */
    public function payIn(PayInRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');

        $this->checkSessionCurrency($user->getCurrency());

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('object_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            TransactionRequest::TRANS_BET,
            $request->input('transaction_id'),
            0 // TODO:: filler - get actual game id from partner
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
     * @return \Illuminate\Http\Response
     */
    public function payOut(PayOutRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'), 'casino');

        $this->checkSessionCurrency($user->getCurrency());

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('object_id'),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            TransactionHelper::amountCentsToWhole($request->input('amount')),
            $request->input('type_operation') === 'rollback'
                ? TransactionRequest::TRANS_REFUND : TransactionRequest::TRANS_WIN,
            $request->input('transaction_id'),
            0 // TODO:: filler - get actual game id from partner
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
     * TODO::refactor gen token based on casino solutions team feedback
     */
    public function genToken(Request $request)
    {
        $casino = $request->input('casino');

        $token = $request->cookie('PHPSESSID');


        //TODO::investigate gen token caller - this is garbage
        $userId = env('TEST_USER_ID');

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

    /**
     * @param string $userCurrency
     */
    protected function checkSessionCurrency(string $userCurrency){
        if($userCurrency !== app('GameSession')->get('currency')){
            throw new UserCurrencyException(409, "Currency mismatch", 1401);
        }
    }
}
