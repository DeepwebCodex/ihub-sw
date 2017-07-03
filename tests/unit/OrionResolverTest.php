<?php
namespace unit;

use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use Codeception\Specify;
use Codeception\Test\Unit;
use Helper\TestUser;
use Illuminate\Support\Facades\Config;
use Nathanmac\Utilities\Parser\Exceptions\ParserException;
use Orion\TestData;
use Exception;
use stdClass;

class OrionResolverTest extends Unit
{

    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;
    private $testUser;
    private $testUser2;
    private $data;

    protected function _before()
    {
        $this->testUser = new TestUser(10);
        $this->testUser2 = new TestUser(660);
        $this->data = new TestData(new \Testing\Accounting\Params('microgaming'));
    }

    protected function _after()
    {
        
    }
    // tests Commit

    /**
     * TODO: Account Manager is not mocked in any test. Please, fix me, Petroff!
     */
    public function testPetroff_TODO()
    {
        throw new \PHPUnit_Framework_SkippedTestError();
    }

//    public function testCommitOne()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111,
//            'currency' => $this->data->currencyMg,
//            'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(),
//            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->init($testData);
//
//        $this->specify("Test correct commit", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $response->finishedDataWin)->count(2);
//            verify("Must be equls zeroe", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    public function testCommitDuplicate()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111, 'currency' => $this->data->currencyMg, 'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->init($testData);
//
//        $this->specify("Test correct commit", function() use($obj) {
//            $this->operation($obj);
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be count two", $response->finishedDataWin)->count(2);
//            verify("Must be equls true", $response->finishedDataWin[0]['isDuplicate'])->equals(1);
//            verify("Response must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    public function testException()
//    {
//        $testData = 'l>';
//        $obj = $this->data->init($testData);
//
//        $this->specify("Test incorrect data from sourse commit", function() use($obj) {
//            $this->expectException(ParserException::class);
//            $obj->source->getData();
//        });
//    }
//
//    public function testException2()
//    {
//        $testData = '<xml><INCORRECT/></xml>';
//        $obj = $this->data->init($testData);
//
//        $this->specify("Test incorrect data from validator data commit", function() use($obj) {
//            $this->expectException(Exception::class);
//            $data = $obj->source->getData();
//            $obj->validatorData->validateBaseStructure($data);
//        });
//    }
//
//    public function testEmptyData()
//    {
//        $testData = [];
//        $obj = $this->data->init($testData);
//
//        $this->specify("Test incorrect data from validator data commit", function() use($obj) {
//            $this->expectException(CheckEmptyValidation::class);
//            $data = $obj->source->getData();
//            $obj->validatorData->validateBaseStructure($data);
//        });
//    }
//
//    // test Rollback
//
//    public function testRollback()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111, 'currency' => $this->data->currencyMg, 'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->initRollback($testData);
//
//        $this->specify("Test correct rollback", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $response->finishedDataWin)->count(2);
//            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    public function testRollbackDuplicate()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111, 'currency' => $this->data->currencyMg, 'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->initRollback($testData);
//
//        $this->specify("Test correct duplicate rollback", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $response->finishedDataWin)->count(2);
//            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//            $responseDuplicate = $this->operation($obj);
//            verify("Must be array", $responseDuplicate->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $responseDuplicate->finishedDataWin)->count(2);
//            verify("Must be equls zero", $responseDuplicate->finishedDataWin[0]['isDuplicate'])->equals(1);
//            verify("Resposne must be array", $responseDuplicate->dataResponse)->containsOnly('array');
//        });
//    }
//
//    // test EndGame
//
//    public function testEndGame()
//    {
//        $obj = $this->data->initEndGame();
//
//        $this->specify("Test correct endgame", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $response->finishedDataWin)->count(2);
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    public function testRollbackWithoutBet()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111, 'currency' => $this->data->currencyMg, 'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->initRollbackWithoutBet($testData);
//
//        $this->specify("Test correct rollback", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Must be  count two", $response->finishedDataWin)->count(1);
//            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    public function testCommitWithoutBet()
//    {
//        $testData[] = [
//            'loginName' => $this->testUser->getUser()->id . $this->data->params->currency,
//            'amount' => 111, 'currency' => $this->data->currencyMg, 'rowId' => $this->data->generateUniqId(),
//            'transactionNumber' => $this->data->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
//            'referenceNumber' => $this->data->generateUniqId()
//        ];
//        $obj = $this->data->initCommitWithoutBet($testData);
//
//        $this->specify("Test correct ccommit", function() use($obj) {
//            $response = $this->operation($obj);
//            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
//            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
//        });
//    }
//
//    private function operation($obj)
//    {
//        $response = new stdClass();
//        $response->data = $obj->source->getData();
//        $obj->validatorData->validateBaseStructure($response->data);
//        $elements = $obj->validatorData->getData($response->data);
//        $response->finishedDataWin = $obj->operationsProcessor->make($elements);
//        $packet = $response->finishedDataWin[$obj->requestResolveData::REQUEST_NAME] ?? [];
//        if ($packet) {
//            $response->dataResponse = $obj->requestResolveData->getData($packet);
//            $obj->validationResolveData->validateBaseStructure($response->dataResponse);
//            $response->finishedDataWin = $packet;
//        }
//        return $response;
//    }
}
