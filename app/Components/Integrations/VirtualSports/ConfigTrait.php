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
        if (!array_has($this->config, $name)) {
            throw new ErrorException('Configuration error', ['config_option' => $name]);
        }
        return array_get($this->config, $name);
    }
}
