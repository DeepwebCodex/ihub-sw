<?php

namespace App\Http\Controllers\Api;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class LiveDealerController extends FundistController
{
    protected function getIntegration()
    {
        return 'liveDealer';
    }

    protected function getObjectIdKey()
    {
        return 'i_actionid';
    }


    protected function getObjectId($objectId): int
    {
        $id = ltrim($objectId, "CD");
        if (!is_numeric($id)) {
            throw new ApiHttpException('400',
                "Wrong format i_actionid: " . $objectId);
        }

        return (int)$id;
    }
}