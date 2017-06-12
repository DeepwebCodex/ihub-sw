<?php
namespace App\Components\Integrations\LiveDealer;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class ObjectId
{
    public static function get($dirtyObjectId)
    {
        $objectId = ltrim($dirtyObjectId, "CD");
        if (!is_numeric($objectId)) {
            throw new ApiHttpException('400',
                "Wrong format i_actionid: " . $dirtyObjectId);
        }

        return (int)$objectId;
    }
}