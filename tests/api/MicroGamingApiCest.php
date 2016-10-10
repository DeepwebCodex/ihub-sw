<?php

class MicroGamingApiCest
{

    private $gameNumber;

    public function _before()
    {

    }

    public function _after()
    {
    }

    // tests
    public function testMethodNotFound(ApiTester $I)
    {
        $I->sendGET('/mg');
        $I->seeResponseCodeIs(400);
        $I->canSeeResponseIsXml();
        $I->expect('both items are in response');
        $I->seeXmlResponseIncludes(" <result seq=\"\" token=\"\" errorcode=\"6000\" errordescription=\"Empty source\"><extinfo/></result>");
    }
}