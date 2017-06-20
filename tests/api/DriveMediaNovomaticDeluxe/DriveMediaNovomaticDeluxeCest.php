<?php

namespace api\NovomaticDeluxe;

use ApiTester;
use App\Models\CommonSerial;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use DriveMedia\NovomaticDeluxe\TestData;
use function GuzzleHttp\json_decode;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use DriveMedia\Helper;

class DriveMediaNovomaticDeluxeCest {

    private $testData;

    /** @var Params  */
    private $params;

    /** @var Helper  */
    private $helper;

    public function __construct() {

        $this->params = new Params('DriveMediaNovomaticDeluxe');
        $this->helper = new Helper($this->params);
        $this->testData = new TestData($this->params);
    }

    public function testMethodNotFound(ApiTester $I) {
        $packet = $this->testData->getDataMethodUnknown();
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSee('{"status":"fail","error":"Unknown method"}');
    }

    public function testGetBalance(ApiTester $I) {

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);

        $packet = $this->testData->getDataGetBalance();
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->canSeeResponseJsonMatchesJsonPath('$.balance');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);
        $I->assertNotEmpty($res->balance);
    }

    public function testWrongPacket(ApiTester $I) {
        $packet = $this->testData->getWrongPacket();
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'fail']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);
        $I->assertNotEmpty($res->error);
    }


    public function testBet(ApiTester $I) {
        $betAmount = 100;
        $winLose = -100;
        $object_id = 12345;
        if($this->params->enableMock) {
            $mockCommonSerial = $this->mock(CommonSerial::class);
            $mockCommonSerial->shouldReceive('getSerial')->withNoArgs()
                ->andReturn($object_id);
        }

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $betAmount)
            ->mock($I);

        $packet = $this->testData->getBetPacket($betAmount, $winLose);
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);

        $I->assertEmpty($res->error);
        $I->assertNotEmpty($res->balance);
        $I->assertNotEmpty($res->operationId);
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'operation_id' => $res->operationId,
            'amount' => $betAmount * 100,
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testWin(ApiTester $I)
    {
        $betAmount = 100;
        $winLose = 140;
        $object_id = 12345;
        if($this->params->enableMock) {
            $mockCommonSerial = $this->mock(CommonSerial::class);
            $mockCommonSerial->shouldReceive('getSerial')->withNoArgs()
                ->andReturn($object_id);
        }

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $betAmount)
            ->win($object_id, $betAmount + $winLose)
            ->mock($I);

        $packet = $this->testData->getWinPacket($betAmount, $winLose);
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);

        $I->assertEmpty($res->error);
        $I->assertNotEmpty($res->balance);
        $I->assertNotEmpty($res->operationId);
        $I->canSeeRecord(Transactions::class, [
            'operation_id' => $res->operationId,
            'amount' => ($betAmount + $winLose) * 100,
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testDuplicate(ApiTester $I)
    {
        $betAmount = 100;
        $winLose = -100;
        $packet = $this->testData->getBetPacket($betAmount, $winLose);
        $object_id = 12345;
        if($this->params->enableMock) {
            $mockCommonSerial = $this->mock(CommonSerial::class);
            $mockCommonSerial->shouldReceive('getSerial')->withNoArgs()
                ->andReturn($object_id);
        }

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $betAmount)
            ->mock($I);

        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRawO = $I->grabResponse();
        $resOriginal = json_decode($resRawO);


        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);

        $I->assertEmpty($res->error);
        $I->assertNotEmpty($res->balance);
        $I->assertNotEmpty($res->operationId);
        $I->assertEquals($resOriginal->balance, $res->balance);
        $I->assertEquals($resOriginal->operationId, $res->operationId);
    }

    public function testWrongSign(ApiTester $I) {
        $packet = $this->testData->getWrongSign();
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'fail', 'error' => 'error_sign']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);
        $I->assertNotEmpty($res->error);
    }

    public function testCaseFloat(ApiTester $I)
    {
        $betAmount = 1.2;
        $winLose = 0.4;
        $object_id = 12345;

        if($this->params->enableMock) {
            $mockCommonSerial = $this->mock(CommonSerial::class);
            $mockCommonSerial->shouldReceive('getSerial')->withNoArgs()
                ->andReturn($object_id);
        }

        (new AccountManagerMock($this->params))
            ->userInfo()
            ->bet($object_id, $betAmount)
            ->win($object_id, $betAmount + $winLose)
            ->mock($I);

        $packet = $this->testData->getFloatPacket($betAmount, $winLose);
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSeeResponseJsonMatchesJsonPath('$.status');
        $I->seeResponseContainsJson(['status' => 'success']);
        $resRaw = $I->grabResponse();
        $res = json_decode($resRaw);

        $I->assertEmpty($res->error);
        $I->assertNotEmpty($res->balance);
        $I->assertNotEmpty($res->operationId);

    }

    private function mock($class)
    {
        $mock = \Mockery::mock($class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial()
        ;
        app()->instance($class, $mock);
        return $mock;
    }

}
