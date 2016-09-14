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

    public function mapping($item)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', 3000);
        $message = $this->useElement('message', 'Unknown');

        $codeMap = CodeMapping::getByErrorCode($code);

        if($codeMap){
            $code = $codeMap['code'];
            $message = ($code == 3000 || ($code == 3100 && $message != 'Unknown')) ? $message : $codeMap['message'];
        }

        $view = [
            'ErrorCode' => $code,
            'ErrorMessage' => $message,
        ];

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }
}