<?php

namespace App\Components\Traits;

use App\Exceptions\ConfigOptionNotFoundException;

/**
 * Trait ConfigTrait
 * @package App\Components\Traits
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
     * @throws ConfigOptionNotFoundException
     */
    protected function getConfigOption(string $name)
    {
        if (!($val = array_get($this->config, $name)) && $val === null) {
            throw new ConfigOptionNotFoundException("Configuration error: key '{$name}'");
        }
        return $val;
    }
}
