<?php

namespace App\Components\Integrations\GameSession;

/**
 * Class Serializer
 * @package App\Components\Integrations\GameSession
 */
trait Serializer
{
    /**
     * Serialize data
     *
     * @param array $data
     * @return string
     */
    protected function serialize(array $data)
    {
        return \json_encode($data);
    }

    /**
     * Unserialize data
     *
     * @param string $data
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        return \json_decode($data, true);
    }
}
