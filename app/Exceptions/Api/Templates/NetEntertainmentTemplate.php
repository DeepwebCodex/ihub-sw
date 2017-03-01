<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\NetEntertainment\CodeMapping;
use App\Components\Integrations\NetEntertainment\StatusCode;
use App\Components\Integrations\NetEntertainment\Hmac;

class NetEntertainmentTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException):array
    {
        $errorCode = $item['code'] ?? StatusCode::UNKNOWN;

        $error = ($errorCode === StatusCode::VALIDATION) ? $item : CodeMapping::getByErrorCode($errorCode);
        $view = [
            'error' => $error['message'] ?? ''
        ];
        $view['hmac'] = (new Hmac($view))->get();

        return $view;
    }
}