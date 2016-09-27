<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\MicroGaming\CodeMapping;
use Carbon\Carbon;
use Stringy\StaticStringy as S;

class MicroGamingTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', 6000);
        $message = $this->useElement('message', 'Неопределенная ошибка.');

        $codeMap = CodeMapping::getByErrorCode($code);

        if($codeMap){
            $code = $codeMap['code'];
            $message = $code == 6000 ? $message : $codeMap['message'];
        }

        $request = request();

        $view = [
            'methodresponse' => [
                '@attributes' => [
                    'name'      => $this->useElement('methodName', $request->input('methodcall.name')),
                    'timestamp' => Carbon::now('UTC')->format("Y/m/d H:i:s.000")
                ],
                'result' => [
                    '@attributes' => [
                        'seq'               => $request->input('methodcall.call.seq'),
                        'token'             => $request->input('methodcall.call.token'),
                        'errorcode'         => $code,
                        'errordescription'  => S::substr($message, 0, 240)
                    ],
                    'extinfo' => []
                ]
            ]
        ];

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}