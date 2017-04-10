<?php

namespace App\Exceptions\Api\Templates;

use App\Components\Integrations\WirexGaming\CodeMapping;

/**
 * Class WirexGamingTemplate
 * @package App\Exceptions\Api\Templates
 */
class WirexGamingTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return mixed
     */
    public function mapping($item, $statusCode, $isApiException): array
    {
        $errorMessage = $item['message'] ?? CodeMapping::SERVER_ERROR;
        return [
            'status' => 'ERROR',
            'code' => $item['code'] ?? 0,
            'message' => $errorMessage
        ];
    }
}
