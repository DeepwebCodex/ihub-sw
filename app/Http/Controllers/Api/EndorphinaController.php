<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\JsonApiFormatter;
use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\Game;
use App\Components\Integrations\Endorphina\StatusCode;
use App\Components\Traits\MetaDataTrait;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\EndorphinaTemplate;
use App\Http\Requests\Endorphina\BalanceRequest;
use App\Http\Requests\Endorphina\BaseRequest;
use App\Http\Requests\Endorphina\BetRequest;
use App\Http\Requests\Validation\EndorphinaValidation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use function app;
use function config;

class EndorphinaController extends BaseApiController
{

    use MetaDataTrait;

    public static $exceptionTemplate = EndorphinaTemplate::class;

    private function prepareUser(): IntegrationUser
    {
        $service_id = $this->getOption('service_id');
        $user = IntegrationUser::get((int) app('GameSession')->get('user_id'), $service_id, 'endorphina');
        EndorphinaValidation::checkCurrency($user->getCurrency(), app('GameSession')->get('currency'));
        return $user;
    }

    public function __construct(JsonApiFormatter $formatter)
    {
        parent::__construct($formatter);
        $this->options = config('integrations.endorphina');

        $this->middleware('check.ip:endorphina');
        Validator::extend('check_sign', 'App\Http\Requests\Validation\EndorphinaValidation@checkSign');
    }

    public function error()
    {
        throw new ApiHttpException(500, 'Unknown method', CodeMapping::getByErrorCode(StatusCode::UNKNOWN_METHOD));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        return parent::respondOk($statusCode, $message, $payload);
    }

    public function session(BaseRequest $request)
    {
        $user = $this->prepareUser();
        return $this->respondOk(Response::HTTP_OK, '', [
                    'player' => $user->id,
                    'currency' => $user->getCurrency(),
                    'game' => Game::getGame((int) app('GameSession')->get('game_id'))
        ]);
    }

    public function balance(BalanceRequest $request)
    {
        $user = $this->prepareUser();
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $user->getBalanceInCents(),
        ]);
    }

    public function bet(BetRequest $request)
    {
        $user = $this->prepareUser();
        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            0,
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
        return $this->respondOk(Response::HTTP_OK, '', [
                    'balance' => $response->getBalanceInCents(),
                    'transactionId' => $response->operation_id
        ]);
    }

}
