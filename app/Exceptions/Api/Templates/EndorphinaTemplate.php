<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\Endorphina\CodeMapping;
use App\Components\Integrations\Endorphina\StatusCode;

class EndorphinaTemplate implements IExceptionTemplate
{

    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException): array
    {
        if (isset($item['message'])) {
            $message = $item['message'];
        } else {
            $error = CodeMapping::getByErrorCode(StatusCode::SERVER_ERROR);
            $message = $error['message'];
        }

        if (isset($item['code'])) {
            $code = CodeMapping::getExternalErrorCode($item['code']);
        } else {
            $code = CodeMapping::getExternalErrorCode(StatusCode::SERVER_ERROR);
        }

        $view = [
            'code' => $code,
            'message' => $message
        ];
        return $view;
    }

}
