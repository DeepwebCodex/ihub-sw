<?php

namespace App\Http\Controllers\Api;

use App\Components\ExternalServices\AccountManager;
use App\Components\ExternalServices\RemoteSession;
use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;
use App\Http\Requests\Simple\AuthRequest;
use App\Http\Requests\Simple\PayInRequest;
use App\Http\Requests\Simple\PayOutRequest;
use Illuminate\Http\Request;
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

        $this->middleware('input.json')->except(['genToken','index']);

        Validator::extend('check_signature', 'App\Http\Requests\Validation\CasinoValidation@CheckSignature');
        Validator::extend('check_time', 'App\Http\Requests\Validation\CasinoValidation@CheckTime');
    }

    public function index(Request $request)
    {
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
        return $this->respondOk(200, "All ok", $request->all());
    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(AuthRequest $request)
    {

    }

    /**
     * @param AuthRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(AuthRequest $request)
    {

    }

    /**
     * @param PayInRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payIn(PayInRequest $request)
    {

    }


    /**
     * @param PayOutRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payOut(PayOutRequest $request)
    {

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
}
