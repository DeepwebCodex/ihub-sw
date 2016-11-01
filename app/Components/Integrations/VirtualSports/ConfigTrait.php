<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\VirtualBoxing\ErrorException;

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
     * @throws \App\Exceptions\Api\VirtualBoxing\ErrorException
     */
    protected function getConfigOption(string $name)
    {
        $res = array_get($this->config, $name);
        if (!$res) {
            throw new ErrorException('Configuration error', ['config_option' => $name]);
        }
        return $res;
    }
}
