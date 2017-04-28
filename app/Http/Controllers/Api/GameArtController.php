<?php

namespace App\Http\Controllers\Api;

use App\Components\Integrations\GameArt\GameArtHelper;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Http\Requests\GameArt\BalanceRequest;
use App\Http\Requests\GameArt\CreditRequest;
use App\Http\Requests\GameArt\DebitRequest;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use App\Exceptions\Api\Templates\GameArtTemplate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use iHubGrid\ErrorHandler\Formatters\JsonApiFormatter;
use iHubGrid\Accounting\Users\IntegrationUser;
use App\Components\Integrations\GameArt\CodeMapping;
use Symfony\Component\HttpFoundation\Response;

class GameArtController extends BaseApiController
{

    public static $exceptionTemplate = GameArtTemplate::class;

    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);
        $this->options = config('integrations.gameart');
        Validator::extend('validate_key', 'App\Http\Requests\Validation\GameArtValidation@validateKey');
    }

    public function index(Request $request)
    {
        $method = GameArtHelper::mapMethod($request->input('action'));
        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function balance(BalanceRequest $request)
    {
        $user = IntegrationUser::get($request->input('remote_id'), $this->getOption('service_id'), 'gameart');

        $remoteData = \GuzzleHttp\json_decode($request->input('remote_data'), true);

        $this->checkCurrency($user->getCurrency(), $remoteData['currency']);

        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => self::toFloat($user->getBalanceInCents())
        ]);
    }

    public function credit(CreditRequest $request)
    {
        $user = IntegrationUser::get($request->input('remote_id'), $this->getOption('service_id'), 'gameart');

        $remoteData = \GuzzleHttp\json_decode($request->input('remote_data'), true);

        $this->checkCurrency($user->getCurrency(), $remoteData['currency']);

        $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'), $request->input('round_id'), $user->id, $user->getCurrency(), GameArtHelper::getTransactionDirection($request->input('action_type')), $request->input('amount'), GameArtHelper::getTransactionType($request->input('action_type')), $request->input('transaction_id'), $request->input('game_id'), $remoteData['partner_id'], $remoteData['cashdesk_id'], $remoteData['user_ip']
        );

        $transactionResponse = GameArtHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => self::toFloat($transactionResponse->getBalanceInCents())
        ]);
    }

    public function debit(DebitRequest $request)
    {
        $user = IntegrationUser::get($request->input('remote_id'), $this->getOption('service_id'), 'gameart');

        $remoteData = \GuzzleHttp\json_decode($request->input('remote_data'), true);

        $this->checkCurrency($user->getCurrency(), $remoteData['currency']);

        $transactionRequest = new TransactionRequest(
                $this->getOption('service_id'), $request->input('round_id'), $user->id, $user->getCurrency(), GameArtHelper::getTransactionDirection($request->input('action_type')), $request->input('amount'), GameArtHelper::getTransactionType($request->input('action_type')), $request->input('transaction_id'), $request->input('game_id'), $remoteData['partner_id'], $remoteData['cashdesk_id'], $remoteData['user_ip']
        );

        $transactionResponse = GameArtHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => self::toFloat($transactionResponse->getBalanceInCents())
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(404, null, CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        $payload = array_merge($payload, [
            'status' => (string) $statusCode
        ]);

        return parent::respondOk($statusCode, $message, $payload);
    }

    protected function checkCurrency($userCurrency, $reqCurrency)
    {
        if ($userCurrency != $reqCurrency) {
            $this->error();
        }
    }

    protected function toFloat(int $balance)
    {
        $balance /= 100;
        return number_format($balance, 2, '.', '');
    }

}
