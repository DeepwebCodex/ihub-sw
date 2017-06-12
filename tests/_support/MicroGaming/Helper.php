<?php


namespace MicroGaming;


use App\Models\MicroGamingObjectIdMap;
use Testing\Accounting\Params;

class Helper
{
    public function __construct(Params $params)
    {
        $this->params = $params;
    }

    public function getPreparedObjectId($game_id)
    {
        return MicroGamingObjectIdMap::getObjectId(
            $this->params->userId,
            $this->params->currency,
            $game_id);
    }

    public function getUniqueNumber()
    {
        return random_int(100000, 9900000);
    }
}