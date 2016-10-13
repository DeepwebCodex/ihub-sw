<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;


use App\Components\Integrations\EuroGamesTech\CodeMapping;

class EuroGamesTechTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', 3000);
        $message = $this->useElement('message', 'Unknown');

        //503 error case
        if(in_array($statusCode, [503, 504])){
            $codeMap = CodeMapping::getByMeaning(CodeMapping::TIMED_OUT);
        } else {
            $codeMap = CodeMapping::getByErrorCode($code);
        }

        if($codeMap){
            $code = $codeMap['code'];
            $message = (($code == 3000 || ($code == 3100 && $message != 'Unknown')) && $isApiException == true) ? $message : $codeMap['message'];
        }

        $balance = $this->useElement('Balance', null);
        $transferId = $this->useElement('CasinoTransferId', null);

        $view = [
            'ErrorCode' => $code,
            'ErrorMessage' => $message,
        ];

        if(!is_null($transferId)) {
            $view = array_merge(['CasinoTransferId' => (int) $transferId],$view);
        }

        if(!is_null($balance)) {
            $view = array_merge(['Balance' => (int) $balance],$view);
        }

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}