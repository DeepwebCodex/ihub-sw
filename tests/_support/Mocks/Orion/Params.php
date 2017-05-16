<?php


namespace Testing\Orion;

use App\Models\DriveMediaAmaticProdObjectIdMap;

class Params
{
    public $enableMock;

    public $currency;
    public $balance;
    public $userId;

    public function __construct()
    {
        $this->enableMock = true;
        $this->userId = 10;
        $this->balance = 1000.34;
        $this->currency = 'EUR';
    }
}