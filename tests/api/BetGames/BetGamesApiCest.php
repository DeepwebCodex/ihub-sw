<?php

namespace api\EuroGamesTech;

//use App\Components\Transactions\TransactionRequest;
//use \App\Components\Integrations\BetGames\Signature;
use \BetGames\TestData;
use \BetGames\TestUser;

class BetGamesApiCest
{

    private $gameNumber;
    private $defenceCode;
    private $data;
    /**
     * @var TestUser
     */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData($this->testUser);
    }

    public function _before()
    {

    }

    public function _after()
    {
    }

    // tests
    public function testMethodNotFound(\ApiTester $I)
    {
    }

    public function testMethodPing(\ApiTester $I)
    {
        $request = $this->data->ping();
        var_dump($request);

        $I->disableMiddleware();
//        $this->defenceCode = (new DefenceCode())->generate($request['PlayerId'], $request['PortalCode'], time());
        $I->sendPOST('/bg/ping', array_merge($request, ['DefenceCode' => 1]));
//        $I->seeResponseCodeIs(200);
//        $I->canSeeResponseIsXml();
//        $I->expect('min required items in response');
//        $I->seeXmlResponseIncludes("<ErrorCode>1000</ErrorCode>");
//        $I->seeXmlResponseIncludes("<ErrorMessage>OK</ErrorMessage>");
//        $I->seeXmlResponseIncludes("<Balance>{$this->testUser->getBalanceInCents()}</Balance>");
    }
}