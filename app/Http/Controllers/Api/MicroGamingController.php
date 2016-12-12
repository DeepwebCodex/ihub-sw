<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\MicroGamingApiFormatter;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Http\Requests\MicroGaming\BalanceRequest;
use App\Http\Requests\MicroGaming\EndGameRequest;
use App\Http\Requests\MicroGaming\LogInRequest;
use App\Http\Requests\MicroGaming\PlayRequest;
use App\Http\Requests\MicroGaming\RefreshTokenRequest;
use Carbon\Carbon;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\MicroGamingTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class CasinoController
 * @package App\Http\Controllers\Api
 */
class MicroGamingController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = MicroGamingTemplate::class;

    public function __construct(MicroGamingApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.microgaming');

        $this->middleware('input.xml');

        Validator::extend('validate_token', 'App\Http\Requests\Validation\MicroGamingValidation@validateToken');
        Validator::extend('validate_play_type', 'App\Http\Requests\Validation\MicroGamingValidation@validatePlayType');
    }

    public function index(Request $request)
    {
        $method = $request->input('methodcall.name', 'error');

        $method = MicroGamingHelper::mapMethod($method);

        if (method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function logIn(LogInRequest $request)
    {
        $user = IntegrationUser::get(app('GameSession')->get('user_id'), $this->getOption('service_id'), 'microgaming');

        $this->addMetaField('currency', $user->getCurrency());

        try
        {
            $token = app('GameSession')->regenerate($request->input('methodcall.call.token'));
        } catch (SessionDoesNotExist $e) {
            throw new ApiHttpException(400, null, CodeMapping::getByMeaning(CodeMapping::TIME_EXPIRED));
        }

        return $this->respondOk(200, '', [
            'loginname'     => $user->id . $user->getCurrency(),
            'currency'      => $user->getCurrency(),
            'country'       => $user->country_id,
            'city'          => $user->city,
            'balance'       => $user->getBalanceInCents(),
            'bonusbalance'  => '0',
            'wallet'        => 'local',
            'idnumber'      => '0',
            'token'         => $token
        ]);
    }

    public function getBalance(BalanceRequest $request)
    {
        $user = IntegrationUser::get(app('GameSession')->get('user_id'), $this->getOption('service_id'), 'microgaming');

        $this->addMetaField('currency', $user->getCurrency());

        return $this->respondOk(200, '', [
            'balance'       => $user->getBalanceInCents(),
            'bonusbalance'  => '0',
        ]);
    }

    public function play(PlayRequest $request)
    {
        $user = IntegrationUser::get(app('GameSession')->get('user_id'), $this->getOption('service_id'), 'microgaming');

        $this->addMetaField('currency', $user->getCurrency());


        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $request->input('methodcall.call.gameid'),
            $user->id,
            $user->getCurrency(),
            MicroGamingHelper::getTransactionDirection($request->input('methodcall.call.playtype')),
            TransactionHelper::amountCentsToWhole($request->input('methodcall.call.amount')),
            MicroGamingHelper::getTransactionType($request->input('methodcall.call.playtype')),
            $request->input('methodcall.call.actionid')
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        $transactionResponse = $transactionHandler->handle(new ProcessMicroGaming());

        return $this->respondOk(200, '', [
            'balance'           => $transactionResponse->getBalanceInCents(),
            'bonusbalance'      => '0',
            'exttransactionid'  => $transactionResponse->operation_id
        ]);
    }

    public function endGame(EndGameRequest $request)
    {
        $user = IntegrationUser::get(app('GameSession')->get('user_id'), $this->getOption('service_id'), 'microgaming');

        $this->addMetaField('currency', $user->getCurrency());

        return $this->respondOk(200, '', [
            'balance'       => $user->getBalanceInCents(),
            'bonusbalance'  => '0',
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $user = IntegrationUser::get(app('GameSession')->get('user_id'), $this->getOption('service_id'), 'microgaming');

        $this->addMetaField('currency', $user->getCurrency());

        return $this->respondOk(200, '', [
            'token' => app('GameSession')->regenerate($request->input('methodcall.call.token'))
        ]);
    }

    public function error()
    {
        throw new ApiHttpException(404, 'Неизвестный метод', CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD));
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = '', array $payload = [])
    {
        $attributes = [
            'seq'   => request()->input('methodcall.call.seq'),
            'token' => request()->input('methodcall.call.token')
        ];

        $attributes = array_merge($attributes, $payload);

        $payload = [
            'methodresponse' => [
                '@attributes' => [
                    'name'      => request()->input('methodcall.name'),
                    'timestamp' => Carbon::now('UTC')->format('Y/m/d H:i:s.000')
                ],
                'result' => [
                    '@attributes' => $attributes,
                    'extinfo' => []
                ]
            ]
        ];

        return parent::respondOk($statusCode, $message, $payload);
    }
}
