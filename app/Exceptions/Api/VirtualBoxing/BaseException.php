<?php

namespace App\Exceptions\Api\VirtualBoxing;

/**
 * Class BaseException
 * @package App\Exceptions\Api\VirtualBoxing
 */
class BaseException extends \RuntimeException
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * BaseException constructor.
     * @param array $payload
     */
    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
