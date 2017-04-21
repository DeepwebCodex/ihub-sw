<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\MrSlotty\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

class MrSlottyTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
    public function mapping($item, $statusCode, $isApiException)
    {
        $codeMap = CodeMapping::getByMeaning(CodeMapping::SERVER_ERROR);
        if(isset($item['code'])) {
            $codeMap = CodeMapping::getByErrorCode($item['code']);
        }
        if($codeMap)
        {
            $code = $codeMap['code'];
            $message = ($item['message']) ? $item['message'] : $codeMap['message'];
        }

        $view = [
            'status' => $statusCode,
            'error' => [
                'code' => $code,
                'message' => $message
            ],
        ];

        return $view;
    }

}