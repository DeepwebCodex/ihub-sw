<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\DriveMediaNovomatic\CodeMapping;
use App\Components\Integrations\DriveMediaNovomatic\NovomaticHelper;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\DriveMedia\ProcessNovomatic;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Components\Users\Interfaces\UserInterface;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\DriveMediaNovomaticTemplate;
use App\Http\Requests\DriveMediaNovomatic\GetBalanceRequest;
use App\Http\Requests\DriveMediaNovomatic\WriteBetRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class DriveMediaNovomaticController
 * @package App\Http\Controllers\Api
 */
class DriveMediaNovomaticController extends BaseApiController
{
    use MetaDataTrait;

    const NODE = 'DriveMediaNovomatic';

    public static $exceptionTemplate = DriveMediaNovomaticTemplate::class;

    /**
     * NovomaticController constructor.
     * @param JsonApiFormatter $formatter
     */
    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.DriveMediaNovomatic');

        $this->middleware('input.json');
        $this->middleware('input.dm.parselogin')->except(['error']);

        Validator::extend('validate_sign', 'App\Http\Requests\Validation\DriveMedia\NovomaticValidation@validateSign');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $method = $request->input('cmd', 'error');

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }
        return app()->call([$this, 'error'], $request->all());
    }

    /**
     * @param UserInterface $user
     * @param $space
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function validateCurrentCurrency(UserInterface $user, string $space)
    {
        if (app()->environment() !== 'production') {
            return;
        }
        $userCurrency = $user->getCurrency();
        $requestCurrency = $this->options['spaces'][$space]['currency'];
        if ($userCurrency !== $requestCurrency) {
            $this->error();
        }
    }

    /**
     * @param GetBalanceRequest $request
     * @return Response
     */
    public function getBalance(GetBalanceRequest $request)
    {
        $userId = $request->get('userId');
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), self::NODE);

        $this->validateCurrentCurrency($user, $request->input('space'));

        return $this->respondOk(200, '', [
            'login' => (string)$request->get('login'),
            'balance' => (string)$user->getBalance(),
        ]);
    }

    /**
     * @param WriteBetRequest $request
     * @return Response
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function writeBet(WriteBetRequest $request)
    {
        $userId = $request->get('userId');
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), self::NODE);

        $this->validateCurrentCurrency($user, $request->input('space'));

        $this->addMetaField('currency', $user->getCurrency());

        $transactions = NovomaticHelper::getTransactions($request->input('bet'), $request->input('winLose'));

        foreach ($transactions as $key => $transaction) {
            $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'),
                0,
                $user->id,
                $user->getCurrency(),
                ($transaction['type'] === 'bet' ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT),
                $transaction['amount'],
                $transaction['type'],
                $request->input('tradeId'),
                $request->input('gameId'),
                $request->get('partnerId'),
                $request->get('cashdeskId'),
                $request->get('userIp')
            );
            $transactionHandler = new TransactionHandler($transactionRequest, $user);
            $transactionResponse = $transactionHandler->handle(new ProcessNovomatic());
            if ($key == 0 && count($transactions) === 2) {
                $user->updateBalance($transactionResponse->getBalanceInCents());
            }
        }
        if (!isset($transactionResponse)) {
            $this->error();
        }
        return $this->respondOk(200, '', [
            'login' => (string)$request->input('login'),
            'balance' => (string)$transactionResponse->getBalance(),
            'operationId' => (string)$transactionResponse->operation_id
        ]);
    }

    /**
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function error()
    {
        throw new ApiHttpException(200, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param array $payload
     * @return Response
     */
    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        $basePayload = [
            'status' => 'success',
            'error' => '',
        ];
        $payload = array_merge($basePayload, $payload);

        return parent::respondOk($statusCode, $message, $payload);
    }
}
