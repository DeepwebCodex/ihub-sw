<?php

namespace unit\Components\Exceptions;


use iHubGrid\BetGames\Http\Middleware\SetPartnerCashdesk;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class SetPartnerCashdeskCest
{

    public function __construct()
    {
        $this->middleware = new SetPartnerCashdesk();
    }

    public function testServices(\UnitTester $I)
    {
        $this->processMiddleware('favbet', $I);
        $this->processMiddleware('favbet-app', $I);
        $this->processMiddleware('favorit', $I);
        $this->processMiddleware('favorit-app', $I);
    }

    private function processMiddleware($service, \UnitTester $I)
    {
        $request = new Request();
        $request->setRouteResolver(function () use ($service){
            return new Route('GET', 'bg/'.$service, function (){});
        });
        $mergedRequest = $this->middleware->handle($request, function ($request){
            return $request;
        });

        $I->assertEquals(config('integrations.betGames.routes.'.$service.'.partner_id'), $mergedRequest->input('partner_id'));
        $I->assertEquals(config('integrations.betGames.routes.'.$service.'.cashdesk_id'), $mergedRequest->input('cashdesk_id'));
    }


}