<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\StatusCode;
use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

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
            $message = $codeMap['message'];
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