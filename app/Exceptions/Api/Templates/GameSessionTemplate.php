<?php

namespace App\Exceptions\Api\Templates;

use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

/**
 * Class GameSessionTemplate
 * @package App\Exceptions\Api\Templates
 */
class GameSessionTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
    public function mapping($item, $statusCode, $isApiException)
    {
        // TODO: Implement mapping() method.
        return [
            'status' => false,
            'message' => data_get($item, 'message', 'Error')
        ];
    }
}
