<?php


namespace DriveMedia;

use Testing\Accounting\Params;

class Helper
{
    public function __construct(Params $params)
    {
        $this->params = $params;
    }

    public function getLogin()
    {
        return $this->params->userId . '--1---5--127-0-0-1';
    }

    public function getTradeId()
    {
        return md5(microtime());
    }
}