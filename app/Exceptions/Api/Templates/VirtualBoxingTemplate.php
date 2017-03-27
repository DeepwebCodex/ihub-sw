<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:47 PM
 */

namespace App\Exceptions\Api\Templates;

/**
 * Class VirtualBoxingTemplate
 * @package App\Exceptions\Api\Templates
 */
class VirtualBoxingTemplate implements IExceptionTemplate
{
    /**
     * @param array $item
     * @param $statusCode
     * @param $isApiException
     * @return array
     */
    public function mapping($item, $statusCode, $isApiException)
    {

        if (isset($item['code'])) {
            unset($item['code']);
        }

        if (isset($item['method'])) {
            $item['method'] = 'f_' . $item['method'];
        }

        if (!$isApiException && isset($item['message'])) {
            $item['message'] = 'Error';
        }

        return $item;
    }
}
