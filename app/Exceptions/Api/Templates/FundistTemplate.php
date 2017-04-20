<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Integrations\Fundist\Hmac;

class FundistTemplate implements IExceptionTemplate
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
        $view['hmac'] = (new Hmac($view, $item['integration'] ?? 'netEntertainment'))->get();

        return $view;
    }
}