<?php

namespace App\Http\Controllers\Api;

use App\Components\ExternalServices\AccountManager;
use App\Components\ExternalServices\RemoteSession;
use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Traits\MetaDataTrait;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;
use App\Http\Requests\Simple\AuthRequest;
use App\Http\Requests\Simple\PayInRequest;
use App\Http\Requests\Simple\PayOutRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->middleware('input.json')->except(['genToken','index']);

        Validator::extend('check_signature', 'App\Http\Requests\Validation\CasinoValidation@CheckSignature');
        Validator::extend('check_time', 'App\Http\Requests\Validation\CasinoValidation@CheckTime');
    }

    public function index(Request $request)
    {
        $user = IntegrationUser::get(1452514, $this->getOption('service_id', 0));
        exit(dump(
            $user->getAttributes()
        ));
        /*exit(dump(
            CasinoHelper::generateActionSignature(['api_id' => 15, 'token' => 'sdfsdfdsfsdfdsfdsfds', 'time' => time()]),
            time()
        ));*/
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->getMetaField('user_id'), $this->getOption('service_id'));
        $user->storeSessionCurrency($user->getCurrency());

        return $this->respondOk(200, 'success', [
            'user_id'   => $user->id,
            'user_name' => $user->login,
            'currency'  => $user->getCurrency(),
            'balance'   => $user->getBalance()
        ]);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'));
        $user->checkSessionCurrency();

        return $this->respondOk(200, 'success', [
           'balance' => $user->getBalance()
        ]);
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(AuthRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'));
        $user->checkSessionCurrency();

        return $this->respondOk();
    }

    /**
     * @param PayInRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payIn(PayInRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'));

        return $this->respondOk(200, 'success', [
            'balance'           => '',
            'transaction_id'    => ''
        ]);
    }


    /**
     * @param PayOutRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payOut(PayOutRequest $request)
    {
        $user = IntegrationUser::get($this->pullMetaField('user_id'), $this->getOption('service_id'));

        return $this->respondOk(200, 'success', [
            'balance'           => '',
            'transaction_id'    => ''
        ]);
    }

    /**
     * @param string $casino
     * @return \Illuminate\Http\JsonResponse
     */
    public function genToken($casino = '')
    {

    }

    public function error(Request $request){
        throw new NotFoundHttpException("Page not found");
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = "", array $payload = []){
        $payload = array_merge([
            'status'    => true,
            'code'      => 1,
            'message'   => 'success',
            'time'      => time()
        ], $payload);

        $payload = array_merge($payload, ['signature' => CasinoHelper::generateActionSignature($payload)]);

        return parent::respondOk($statusCode, $message, $payload);
    }
}
