<?php
namespace App\Http\Controllers\Api;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\Igrosoft\IgrosoftHelper;
use App\Components\Transactions\Strategies\DriveMedia\ProcessIgrosoft;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\DriveMediaTemplate;
use App\Http\Requests\DriveMedia\Igrosoft\BalanceRequest;
use App\Http\Requests\DriveMedia\Igrosoft\PlayRequest;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use iHubGrid\ErrorHandler\Formatters\JsonApiFormatter;

/**
 * Class DriveMediaIgrosoftController
 * @package App\Http\Controllers\Api
 */
class DriveMediaIgrosoftController extends BaseApiController
{
    /** @var string  */
    public static $exceptionTemplate = DriveMediaTemplate::class;

    /**
     * DriveMediaIgrosoftController constructor.
     * @param JsonApiFormatter $formatter
     */
    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.DriveMediaIgrosoft');

        $this->middleware('check.ip:DriveMediaIgrosoft');
        $this->middleware('input.json')->except(['error']);
        $this->middleware('input.dm.parselogin')->except(['error']);

        Validator::extend('validate_space', 'App\Http\Requests\Validation\DriveMedia\IgrosoftValidation@validateSpace');
        Validator::extend('validate_sign', 'App\Http\Requests\Validation\DriveMedia\IgrosoftValidation@validateSign');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $method = IgrosoftHelper::mapMethod($request->input('cmd'));

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    /**
     * @param BalanceRequest $request
     * @return Response
     */
    public function balance(BalanceRequest $request)
    {
        $user = IntegrationUser::get($request->input('userId'), $this->getOption('service_id'), 'DriveMediaIgrosoft');

        IgrosoftHelper::checkCurrency($user->getActiveWallet()->currency, $request->input('space'));

        return $this->respondOk(200, null, [
            'login' => $request->input('login'),
            'balance' => money_format('%i', $user->getBalance())
        ]);
    }

    /**
     * @param PlayRequest $request
     * @return Response
     */
    public function bet(PlayRequest $request)
    {
        $user = IntegrationUser::get($request->input('userId'), $this->getOption('service_id'), 'DriveMediaIgrosoft');

        IgrosoftHelper::checkCurrency($user->getActiveWallet()->currency, $request->input('space'));

        $transactions = IgrosoftHelper::getTransactions($request->input('bet'), $request->input('winLose'), $request->input('betInfo'));

        foreach ($transactions as $key => $transaction) {
            $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'),
                0,
                $user->id,
                $user->getCurrency(),
                ($transaction['type'] == "bet" ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT),
                $transaction['amount'],
                $transaction['type'],
                $request->input('tradeId'),
                $request->input('gameId'),
                $request->input('partnerId'),
                $request->input('cashdeskId'),
                $request->input('userIp')
            );

            $transactionHandler = new TransactionHandler($transactionRequest, $user);

            $transactionResponse = $transactionHandler->handle(new ProcessIgrosoft());

            if($key == 0 && sizeof($transactions) == 2) {
                $user->updateBalance($transactionResponse->getBalanceInCents());
            }
        }

        return $this->respondOk(200, null, [
            'login'         => $request->input('login'),
            'balance'       => (string)money_format('%i', $transactionResponse->getBalance()),
            'operationId'   => (string)$transactionResponse->operation_id
        ]);
    }

    public function error() {
        throw new ApiHttpException(500, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    /**
     * @param int $statusCode
     * @param string|null $message
     * @param array $payload
     * @return Response
     */
    public function respondOk($statusCode = Response::HTTP_OK, string $message = null, array $payload = [])
    {
        $payload = array_merge($payload, [
            'status' => 'success',
            'error' => ''
        ]);

        return parent::respondOk($statusCode, '', $payload);
    }
}