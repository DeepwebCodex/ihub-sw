<?php

namespace App\Http\Controllers\Api;


class NetEntertainmentController extends FundistController
{
    protected function setIntegration()
    {
        $this->integration = 'netEntertainment';
    }
}