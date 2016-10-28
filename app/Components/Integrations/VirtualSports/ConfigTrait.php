<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\ApiHttpException;

/**
 * Trait ConfigTrait
 * @package App\Components\Integrations\VirtualSports
 */
trait ConfigTrait
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @param string $name
     * @return mixed
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    protected function getConfigOption(string $name)
    {
        $res = array_get($this->config, $name);
        if (!$res) {
            throw new ApiHttpException(400, 'config error');
        }
        return $res;
    }
}
