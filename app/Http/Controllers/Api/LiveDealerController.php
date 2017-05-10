<?php

namespace App\Http\Controllers\Api;

class LiveDealerController extends FundistController
{
    protected function getIntegration()
    {
        return 'liveDealer';
    }
}