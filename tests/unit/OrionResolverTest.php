<?php

use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use Codeception\Specify;
use Codeception\Test\Unit;
use Helper\TestUser;
use Illuminate\Support\Facades\Config;
use Nathanmac\Utilities\Parser\Exceptions\ParserException;
use Orion\TestData;

class OrionResolverTest extends Unit {

    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;
    private $testUser;
    private $testUser2;
    private $data;

    protected function _before() {
        $this->testUser = new TestUser(10);
        $this->testUser2 = new TestUser(660);
        $this->data = new TestData();
    }

    protected function _after() {
        
    }

    // tests Commit


    public function testCommit() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111,
            'currency' => $this->testUser->getCurrency(),
            'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(),
            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->init($testData);

        $this->specify("Test correct commit", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zeroe", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testCommitDuplicate() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->init($testData);

        $this->specify("Test correct commit", function() use($obj) {
            $this->data->operation($obj);
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be count two", $response->finishedDataWin)->count(2);
            verify("Must be equls true", $response->finishedDataWin[0]['isDuplicate'])->equals(1);
            verify("Response must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testException() {
        $testData = 'l>';
        $obj = $this->data->init($testData);

        $this->specify("Test incorrect data from sourse commit", function() use($obj) {
            $this->expectException(ParserException::class);
            $obj->source->getData();
        });
    }

    public function testException2() {
        $testData = '<xml><INCORRECT/></xml>';
        $obj = $this->data->init($testData);

        $this->specify("Test incorrect data from validator data commit", function() use($obj) {
            $this->expectException(Exception::class);
            $data = $obj->source->getData();
            $obj->validatorData->validateBaseStructure($data);
        });
    }

    public function testEmptyData() {
        $testData = [];
        $obj = $this->data->init($testData);

        $this->specify("Test incorrect data from validator data commit", function() use($obj) {
            $this->expectException(CheckEmptyValidation::class);
            $data = $obj->source->getData();
            $obj->validatorData->validateBaseStructure($data);
        });
    }

    // test Rollback

    public function testRollback() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->initRollback($testData);

        $this->specify("Test correct rollback", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testRollbackDuplicate() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->initRollback($testData);

        $this->specify("Test correct duplicate rollback", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
            $responseDuplicate = $this->data->operation($obj);
            verify("Must be array", $responseDuplicate->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $responseDuplicate->finishedDataWin)->count(2);
            verify("Must be equls zero", $responseDuplicate->finishedDataWin[0]['isDuplicate'])->equals(1);
            verify("Resposne must be array", $responseDuplicate->dataResponse)->containsOnly('array');
        });
    }

    // test EndGame

    public function testEndGame() {
        $obj = $this->data->initEndGame();

        $this->specify("Test correct endgame", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testRollbackWithoutBet() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->initRollbackWithoutBet($testData);

        $this->specify("Test correct rollback", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(1);
            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }
    
    public function testCommitWithoutBet() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->data->generateUniqId(),
            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->data->generateUniqId()
        ];
        $obj = $this->data->initCommitWithoutBet($testData);

        $this->specify("Test correct ccommit", function() use($obj) {
            $response = $this->data->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  empty", $response->finishedDataWin)->isEmpty();
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

}
