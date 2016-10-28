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

        if (!$isApiException && isset($this->item['message'])) {
            $this->item['message'] = 'Error';
            if (isset($this->item['code'])) {
                unset($this->item['code']);
            }
        }

        return $this->item;
    }
}
