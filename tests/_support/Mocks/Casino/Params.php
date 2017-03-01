<?php


namespace Testing\Casino;


class Params
{
    const BIG_AMOUNT = 1000000;
    const AMOUNT = 1;
    const CURRENCY = 'EUR';
    const BALANCE = 100;
    const OBJECT_ID = 1234;
    const NO_BET_OBJECT_ID = 12345;
    const STORAGE_PENDING_OBJECT_ID = 123456;
    const ZERO_WIN_OBJECT_ID = 1234567;

    public $enableMock = true;

    public function getObjectId($case = null)
    {
        if($this->enableMock){
            return $case ?? self::OBJECT_ID;
        }

        return random_int(100000, 9900000);
    }
}