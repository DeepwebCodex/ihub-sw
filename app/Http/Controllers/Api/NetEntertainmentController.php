<?php

namespace App\Http\Controllers\Api;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class NetEntertainmentController extends FundistController
{
    protected function getIntegration()
    {
        return 'netEntertainment';
    }

    protected function getObjectIdKey()
    {
        return 'i_gameid';
    }

    protected function getObjectId($objectId): int
    {
        return (int)$objectId;
    }
}