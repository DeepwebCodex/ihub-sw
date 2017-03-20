<?php

namespace api\NovomaticDeluxe;

use ApiTester;
use App\Components\Transactions\TransactionRequest;
use App\Models\Transactions;
use DriveMedia\NovomaticDeluxe\TestData;
use DriveMedia\TestUser;
use function GuzzleHttp\json_decode;

class DriveMediaNovomaticDeluxeCest {

    private $testData;

    /** @var TestUser */
    private $testUser;

    public function __construct() {

        $this->testUser = new TestUser();

        $this->testData = new TestData($this->testUser);
    }

    public function _before(ApiTester $I) {
        //$I->disableMiddleware();
    }

    public function testMethodNotFound(ApiTester $I) {
        $packet = $this->testData->getDataMethodUnknown();
        $I->sendPOST('/nvmd', $packet);
        $I->canSeeResponseIsJson();
        $I->canSee('{"status":"fail","error":"Unknown method"}');
    }

    public function testGetBalance(ApiTester $I) {
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
        $packet = $this->testData->getBetPacket();
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
            'amount' => 10023,
            'transaction_type' => TransactionRequest::TRANS_BET,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_WITHDRAWAL
        ]);
    }

    public function testWin(ApiTester $I) {
        $packet = $this->testData->getWinPacket();
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
            'amount' => 11423,
            'transaction_type' => TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => TransactionRequest::D_DEPOSIT
        ]);
    }

    public function testDuplicate(ApiTester $I) {
        $packet = $this->testData->getBetPacket();
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


    public function testCaseFloat(ApiTester $I) {

        $packet = $this->testData->getFloatPacket();
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

}
