<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;


use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\Casino\CodeMapping;

class CasinoTemplate implements IExceptionTemplate
{
    private $strict = false;

    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', 0);
        $message = $this->useElement('message', 'Unknown');

        $codeMap = CodeMapping::getByErrorCode($code);

        if($codeMap){
            $code = $codeMap['code'];
            $message = ($code == 0 && $isApiException === true) ? $message : $codeMap['message'];
        }

        $view = [
            'status' => false,
            'code' => $code,
            'message' => $message,
            'token' => $this->useElement('token', app('Request')::input('token', '')),
            'signature' => CasinoHelper::generateActionSignature([]),
            'time' => time()
        ];

        if(!$this->strict){
            $view = array_merge($view, $this->item);
        }

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}