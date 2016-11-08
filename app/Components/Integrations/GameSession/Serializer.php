<?php

namespace App\Components\Integrations\GameSession;

/**
 * Class Serializer
 * @package App\Components\Integrations\GameSession
 */
trait Serializer
{
    /**
     * @param array $sessionData
     * @return string
     */
    protected function serialize(array $sessionData)
    {
        return \json_encode($sessionData);
    }

    /**
     * @param string $sessionData
     * @return mixed
     */
    protected function unserialize(string $sessionData)
    {
        return \json_decode($sessionData, true);
    }
}
