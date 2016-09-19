<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 3:45 PM
 */

namespace App\Exceptions\Api\Templates;


interface IExceptionTemplate
{
    /**
     * @param array $item
     * @return array
     */
    public function mapping($item, $statusCode);
}