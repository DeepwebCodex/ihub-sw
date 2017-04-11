<?php

namespace App\Http\Controllers\Api;

class LiveDealerController extends FundistController
{
    protected function setIntegration()
    {
        $this->integration = 'liveDealer';
    }
}