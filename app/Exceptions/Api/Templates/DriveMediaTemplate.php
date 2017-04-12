<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\DriveMedia\CodeMapping;
use App\Components\Integrations\DriveMedia\StatusCode;

/**
 * Class DriveMediaTemplate
 * @package App\Exceptions\Api\Templates
 */
class DriveMediaTemplate implements IExceptionTemplate
{
    private $item;

    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
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

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    private function useElement($key, $default){
        $val = isset($this->item[$key]) ? $this->item[$key] : $default;
        unset($this->item[$key]);

        return $val;
    }

}