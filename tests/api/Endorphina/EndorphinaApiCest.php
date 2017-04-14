<?php

namespace api\BetGames;

use App\Components\Integrations\BetGames\CodeMapping;
use App\Components\Integrations\BetGames\Signature;
use App\Components\Integrations\BetGames\StatusCode;
use App\Components\Integrations\GameSession\GameSessionService;
use App\Components\Transactions\Strategies\BetGames\ProcessBetGames;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\GenericApiHttpException;
use App\Models\Transactions;
use \BetGames\TestData;
use \BetGames\TestUser;
use Codeception\Scenario;
use Testing\GameSessionsMock;

/**
 * Class BetGamesApiCest
 * @package api\BetGames
 */
class EndorphinaApiCest
{

    private $data;

    /** @var TestUser */
    private $testUser;

    public function __construct()
    {
        $this->testUser = new TestUser();
        $this->data = new TestData();
    }

    public function _before(\ApiTester $I, Scenario $s)
    {
        $I->mockAccountManager($I, config('integrations.betGames.service_id'));

        if (!in_array($s->getFeature(), self::OFFLINE)) {
            $I->getApplication()->instance(GameSessionService::class, GameSessionsMock::getMock());
            $I->haveInstance(GameSessionService::class, GameSessionsMock::getMock());
        }
    }

    private function mock($class)
    {
        $mock = \Mockery::mock($class)
                ->shouldAllowMockingProtectedMethods()
                ->makePartial();
        app()->instance($class, $mock);
        return $mock;
    }

    public function testMethodNotFound(\ApiTester $I)
    {
        $I->sendPOST('/bg/favbet/', $this->data->notFound());
        $this->getResponseFail($I, StatusCode::UNKNOWN);
    }

    public function testPing(\ApiTester $I)
    {
        $I->sendPOST('/bg/favbet/', $this->data->ping());
        $this->getResponseOk($I);
    }

    private function getResponseOk(\ApiTester $I)
    {
        $I->seeResponseCodeIs(200);

        $data = $this->responseToArray($I);

        $signatureValidation = $this->validateSignature(
                array_get($data, 'signature'), $data, $this->data->partnerId, $this->data->cashdeskId
        );

        $I->assertTrue($signatureValidation);

        $I->assertArrayHasKey('method', $data);
        $I->assertArrayHasKey('token', $data);
        $I->assertArrayHasKey('success', $data);
        $I->assertArrayHasKey('error_code', $data);
        $I->assertArrayHasKey('error_text', $data);
        $I->assertArrayHasKey('params', $data);
        if (in_array($data['method'], ['transaction_bet_payin', 'transaction_bet_payout'])) {
            $I->assertArrayHasKey('already_processed', $data['params']);
            $I->assertArrayHasKey('balance_after', $data['params']);
            $I->assertNotNull($data['params']['balance_after']);
            $I->assertNotNull($data['params']['already_processed']);
        }
        $I->assertEquals(1, $data['success']);

        return $data;
    }

    private function getResponseFail(\ApiTester $I, $errorCode)
    {
        $data = $this->responseToArray($I);

        $signatureValidation = $this->validateSignature(
                array_get($data, 'signature'), $data, $this->data->partnerId, $this->data->cashdeskId
        );

        $I->assertTrue($signatureValidation);

        $I->assertArrayHasKey('method', $data);
        $I->assertArrayHasKey('token', $data);
        $I->assertArrayHasKey('success', $data);
        $I->assertArrayHasKey('error_code', $data);
        $I->assertArrayHasKey('error_text', $data);
        $I->assertEquals(0, $data['success']);
        $error = CodeMapping::getByErrorCode($errorCode);
        $I->assertEquals($error['code'], $data['error_code']);

        return $data;
    }

    private function isRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can see record of transaction applied');
        $I->canSeeRecord(Transactions::class, [
            'foreign_id' => $request['params']['transaction_id'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function noRecord(\ApiTester $I, $request, $method)
    {
        $I->expect('Can`t see record of transaction applied');
        $I->cantSeeRecord(Transactions::class, [
            'foreign_id' => $request['params']['transaction_id'],
            'transaction_type' => ($method == 'bet') ? TransactionRequest::TRANS_BET : TransactionRequest::TRANS_WIN,
            'status' => TransactionRequest::STATUS_COMPLETED,
            'move' => ($method == 'bet') ? TransactionRequest::D_WITHDRAWAL : TransactionRequest::D_DEPOSIT
        ]);
    }

    private function responseToArray(\ApiTester $I)
    {
        $xml = new \SimpleXMLElement($I->grabResponse());
        $result = json_decode(json_encode((array) $xml), 1);

        if (isset($result['error_text']) && is_array($result['error_text']) && count($result['error_text']) === 0) {
            $result['error_text'] = '';
        }

        return $result;
    }

    private function validateSignature($signature, $data, $partnerId, $cashdeskId)
    {
        unset($data['signature']);

        $generatedSignature = new Signature($data, $partnerId, $cashdeskId);
        $this->signature = $generatedSignature->getHash();
        return !$generatedSignature->isWrong($signature);
    }

}
