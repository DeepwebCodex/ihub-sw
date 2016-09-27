<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\MicroGamingApiFormatter;
use App\Components\Integrations\Casino\CodeMapping;
use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Traits\MetaDataTrait;
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

        $this->options = config('integrations.microGaming');

        $this->middleware('input.xml')->except(['test']);
    }

    public function index(Request $request)
    {
        $method = $request->input('methodcall.name', 'error');

        $method = MicroGamingHelper::mapMethod($method);

        if(method_exists($this, $method)) {
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    public function logIn(LogInRequest $request)
    {
        return $this->respondOk(200, "", [
            'loginname'     => '',
            'currency'      => '',
            'country'       => '',
            'city'          => '',
            'balance'       => '',
            'bonusbalance'  => '',
            'wallet'        => '',
            'idnumber'      => ''
        ]);
    }

    public function getBalance(BalanceRequest $request)
    {
        return $this->respondOk(200, "", [

        ]);
    }

    public function play(PlayRequest $request)
    {
        return $this->respondOk(200, "", [

        ]);
    }

    public function endGame(EndGameRequest $request)
    {
        return $this->respondOk(200, "", [

        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        return $this->respondOk(200, "", [

        ]);
    }

    public function test(Request $request){
        //exit(dump($request->all()));

        return $this->respondOk(201, "After Redirect", [
            'test' => 1
        ]);
    }

    public function error(){
        throw new ApiHttpException(404, null, [
            'code'      => array_get('code', CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD)),
            'message'   => 'Неизвестный метод'
        ]);
    }

    public function respondOk($statusCode = Response::HTTP_OK, string $message = "", array $payload = []){

        $attributes = [
            'seq'   => request()->input('methodcall.call.seq'),
            'token' => request()->input('methodcall.call.token') //TODO::generate token
        ];

        $attributes = array_merge($attributes, $payload);

        $payload = [
            'methodresponse' => [
                '@attributes' => [
                    'name'      => request()->input('methodcall.name'),
                    'timestamp' => Carbon::now('UTC')->format("Y/m/d H:i:s.000")
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
