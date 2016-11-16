<?php

namespace App\Exceptions\Api\Templates;


use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\Signature;
use \App\Components\Integrations\EuroGamesTech\StatusCode;

class BetGamesTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
//        var_dump($item, $statusCode); die('qwe');
        $this->item = $item;

        $code = (int)$this->useElement('code', StatusCode::INTERNAL_SERVER_ERROR);
        $message = $this->useElement('message', 'Unknown');

        //503 error case
        if(in_array($statusCode, [503, 504])){
            $codeMap = CodeMapping::getByMeaning(CodeMapping::TIMED_OUT);
        } else {
            $codeMap = CodeMapping::getByErrorCode($code);
        }

        if($codeMap){
            $code = $codeMap['code'];
            $message = (($code == StatusCode::INTERNAL_SERVER_ERROR ||
                    ($code == StatusCode::INSUFFICIENT_FUNDS && $message != 'Unknown')) && $isApiException == true) ? $message : $codeMap['message'];
        }

        $view = [
            'method' => 'ping',
            'token' => '-',
            'success' => 0,
            'error_code' => 1,
            'error_text' => $item['message'],
            'time' => time(),
        ];
        $signature = new Signature($view);

        return array_merge($view, [
            'params' => '',
            'signature' => $signature->getHash(),
        ]);
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}