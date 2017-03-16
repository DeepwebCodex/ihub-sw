<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\StatusCode;

class DriveMediaTemplate implements IExceptionTemplate
{
    private $item;

    public function mapping($item, $statusCode, $isApiException)
    {
        $this->item = $item;

        $code = (int)$this->useElement('code', StatusCode::INTERNAL_SERVER_ERROR);
        $message = $this->useElement('message', 'Unknown');

        $codeMap = CodeMapping::getByErrorCode($code);

        if($codeMap){
            $code = $codeMap['code'];
            $message = ($code == 6000 && $isApiException === true) ? $message : $codeMap['message'];
        }

        $view = [
            'status'    => 'fail',
            'error'     => $message
        ];

        return $view;
    }

    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }

}