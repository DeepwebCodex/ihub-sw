<?php

use App\Components\Integrations\MicroGaming\Orion\ProcessOperations;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\ThirdParty\Array2Xml;
use App\Http\Requests\Validation\Orion\CommitValidation;
use Carbon\Carbon;
use Codeception\Specify;
use Codeception\Test\Unit;
use Helper\TestUser;
use Nathanmac\Utilities\Parser\Exceptions\ParserException;

class OrionResolverTest extends Unit {

    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;
    private $testUser;
    private $testUser2;

    protected function _before() {
        $this->testUser = new TestUser(10);
        $this->testUser2 = new TestUser(660);
    }

    protected function _after() {
        
    }

    protected function generatedMockXml($userArray, $count = 1) {
        $tmpArray = array();
        foreach ($userArray as $key => $value) {
            for ($i = 0; $i <= $count; $i++) {
                extract($value);
                $tmpArray[] = [
                    'a:LoginName' => $loginName,
                    'a:UserId' => $this->generateUniqId(),
                    'a:ChangeAmount' => $amount,
                    'a:TransactionCurrency' => $currency,
                    'a:Status' => 'Unknown',
                    'a:RowId' => $rowId,
                    'a:TransactionNumber' => $transactionNumber,
                    'a:GameName' => 'MGS_TombRaider',
                    'a:DateCreated' => Carbon::now('UTC')->format('Y/m/d H:i:s.000'),
                    'a:MgsReferenceNumber' => $referenceNumber,
                    'a:ServerId' => $serverId,
                    'a:MgsPayoutReferenceNumber' => $referenceNumber,
                    'a:PayoutAmount' => $amount,
                    'a:ProgressiveWin' => false,
                    'a:ProgressiveWinDesc' => '',
                    'a:FreeGameOfferName' => '',
                    'a:TournamentId' => 0,
                    'a:Description' => '',
                    'a:ExtInfo' => '',
                    'a:RowIdLong' => $rowId,
                ];
            }
        }


        $data = [
            '@attributes' => [
                'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
            ],
            's:Body' => [
                'GetCommitQueueDataResponse' => [
                    '@attributes' => [
                        'xmlns' => 'http://mgsops.net/AdminAPI_Admin'
                    ],
                    'GetCommitQueueDataResult' => [
                        '@attributes' => [
                            'xmlns:a' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures',
                            'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance'
                        ],
                        'a:QueueDataResponse' => $tmpArray
                    ]
                ]
            ]
        ];
        return Array2Xml::createXML('s:Envelope', $data)->saveXML();
    }

    private function generateUniqId() {
        return mt_rand(100000, 999999);
    }

    // tests
    public function testGetCommitQueueData() {

        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(), 'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'), 'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock);
        $this->specify("Test get commit QueueData", function() use($commit) {
            $t = $commit->getData();
            verify("Must be fill", $t)->notEmpty();
            verify("Must be DOMNodeList", $t)->containsOnly('array');
        });


        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue('wrong xml'));
        $commit = new GetCommitQueueData($clientMock);
        $this->specify("Test commit QueueData when source empty", function() use($commit) {
            $this->expectException(ParserException::class);
            $t = $commit->getData();
        });
    }

    public function testValidationCommitData() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(), 'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'), 'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock);

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $this->specify("Test validation commit data", function() use($data, $validatorCommitData) {
            verify("Validation passed", $validatorCommitData->validateBaseStructure($data))->true();
        });
    }

    public function testProsseccTransaction() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(), 'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'), 'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock);

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $validatorCommitData->validateBaseStructure($data);

        $this->specify("Test process commit operation", function() use($data) {
            $handleCommit = ProcessOperations::commit($data);
        });
    }

}
