<?php

namespace App\Http\Controllers\Api;

use App\Components\Integrations\LiveDealer\ObjectId;

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
        return ObjectId::get($objectId);
    }
}