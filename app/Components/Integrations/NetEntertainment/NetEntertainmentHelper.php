<?php

namespace App\Components\Integrations\NetEntertainment;

use App\Models\NetEntertainmentObjectIdMap;

/**
 * Class NetEntertainmentHelper
 * @package App\Components\Integrations\NetEntertainment
 */
class NetEntertainmentHelper
{
    /**
     * @param int $gameId
     * @param string $actionId
     * @return int
     */
    public static function getObjectIdMap(int $gameId, string $actionId): int
    {
        return NetEntertainmentObjectIdMap::getObjectId($gameId, $actionId);
    }
}
