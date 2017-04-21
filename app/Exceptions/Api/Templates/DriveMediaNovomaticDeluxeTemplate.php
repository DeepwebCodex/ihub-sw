<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\DriveMediaNovomaticDeluxe\CodeMapping;
use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

class DriveMediaNovomaticDeluxeTemplate implements IExceptionTemplate {

    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException): array {
        if (isset($item['message'])) {
            $error_message = $item['message'];
        }else{
            $error_message = CodeMapping::SERVER_ERROR;
        }
        $view = [
            'status' => 'fail',
            'error' => $error_message
        ];
        return $view;
    }

}
