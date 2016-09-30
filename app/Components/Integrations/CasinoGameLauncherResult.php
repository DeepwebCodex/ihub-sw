<?php

namespace App\Components\Integrations;

/**
 * Class CasinoGameLauncherResult
 * @package App\Components\ExternalServices
 */
class CasinoGameLauncherResult
{
    /**
     * @var bool
     */
    protected $status;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $url;

    /**
     * CasinoGameLauncherResult constructor.
     * @param $status
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
