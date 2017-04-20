<?php

namespace App\Http\Controllers\Api;

class LiveDealerController extends FundistController
{
    protected function setIntegration()
    {
        return 'liveDealer';
    }
}