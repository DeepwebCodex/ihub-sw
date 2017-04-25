<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;

use iHubGrid\ErrorHandler\Exceptions\Api\Templates\IExceptionTemplate;

/**
 * Class VirtualBoxingTemplate
 * @package App\Exceptions\Api\Templates
 */
class InspiredVirtualGamingTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
    public function mapping($item, $statusCode, $isApiException)
    {
        $message = config('app.debug') ? array_get($item, 'message', 'BADFORMAT') : 'BADFORMAT';

        return [
            'message' => $message
        ];
    }
}
