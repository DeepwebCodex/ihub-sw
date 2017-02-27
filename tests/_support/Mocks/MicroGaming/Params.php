<?php


namespace Testing\MicroGaming;


use App\Models\MicroGamingObjectIdMap;

class Params
{
    const BIG_AMOUNT = 1000000;
    const AMOUNT = 10;
    const JACKPOT_AMOUNT = 30;
    const CURRENCY = 'EUR';
    const BALANCE = 100;
    const OBJECT_ID = 1234;
    const NO_BET_OBJECT_ID = 12345;
    const DUPLICATED_BET_OBJECT_ID = 123451;
    const STORAGE_PENDING_OBJECT_ID = 123456;
    const ZERO_BET_OBJECT_ID = 12345670;
    const ZERO_WIN_OBJECT_ID = 12345671;
    const ZERO_WIN_OPERATION_ID = 12345678;
    const MULTI_WIN_OBJECT_ID = 12340;

    public $enableMock = 1;
    public $userId;


    public function getObjectId($case = null)
    {
        if ($this->enableMock) {
            return $case ?? self::OBJECT_ID;
        }

        return random_int(100000, 9900000);
    }

    public function getAmount()
    {
        return self::AMOUNT * 100;
    }

    public function getJackpotAmount()
    {
        return self::JACKPOT_AMOUNT * 100;
    }

    public function getPreparedObjectId($game_id)
    {
        return MicroGamingObjectIdMap::getObjectId(
            env('TEST_USER_ID'),
            self::CURRENCY,
            $game_id);
    }
}