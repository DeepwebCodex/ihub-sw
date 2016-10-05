<?php


class ExperimentApiCest
{

    protected function _before()
    {

    }

    protected function _after()
    {
    }

    // tests
    public function testApiPrimitive(ApiTester $I)
    {
        $I->sendGET('/casino');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->expect('both items are in response');
        $I->seeResponseContainsJson(['status' => false, 'message' => 'Unknown imprint', 'token' => '']);
    }
}