<?php

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Integrations\MicroGaming\Orion\CommitProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\GetRollbackQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\RollbackProcessor;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Components\ThirdParty\Array2Xml;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use App\Http\Requests\Validation\Orion\RollbackValidation;
use App\Models\MicroGamingObjectIdMap;
use Carbon\Carbon;
use Codeception\Specify;
use Codeception\Test\Unit;
use Helper\TestUser;
use Illuminate\Support\Facades\Config;
use Nathanmac\Utilities\Parser\Exceptions\ParserException;

class OrionResolverTest extends Unit {

    use Specify;

    /**
     * @var UnitTester
     */
    protected $tester;
    private $testUser;
    private $testUser2;
    private $sampleCommitQ = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetCommitQueueDataResponse xmlns="http://mgsops.net/AdminAPI_Admin"><GetCommitQueueDataResult xmlns:a="http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>6750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33740</a:RowId><a:TransactionNumber>4</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:21:50 PM</a:DateCreated><a:MgsReferenceNumber>9000033724</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33740</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>9950</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33748</a:RowId><a:TransactionNumber>6</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:30 PM</a:DateCreated><a:MgsReferenceNumber>9000033732</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33748</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33751</a:RowId><a:TransactionNumber>8</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:54 PM</a:DateCreated><a:MgsReferenceNumber>9000033735</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33751</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33758</a:RowId><a:TransactionNumber>11</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:25:46 PM</a:DateCreated><a:MgsReferenceNumber>9000033742</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33758</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33765</a:RowId><a:TransactionNumber>17</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:26:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033749</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33765</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33767</a:RowId><a:TransactionNumber>18</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:27:09 PM</a:DateCreated><a:MgsReferenceNumber>9000033751</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33767</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33776</a:RowId><a:TransactionNumber>22</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:26 PM</a:DateCreated><a:MgsReferenceNumber>9000033760</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33776</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33778</a:RowId><a:TransactionNumber>23</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:52 PM</a:DateCreated><a:MgsReferenceNumber>9000033762</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33778</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33784</a:RowId><a:TransactionNumber>28</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:35:58 PM</a:DateCreated><a:MgsReferenceNumber>9000033768</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33784</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33787</a:RowId><a:TransactionNumber>30</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:18 PM</a:DateCreated><a:MgsReferenceNumber>9000033771</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33787</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33792</a:RowId><a:TransactionNumber>33</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033776</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33792</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>250</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33799</a:RowId><a:TransactionNumber>36</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:38:13 PM</a:DateCreated><a:MgsReferenceNumber>9000033783</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33799</a:RowIdLong></a:QueueDataResponse></GetCommitQueueDataResult></GetCommitQueueDataResponse></s:Body></s:Envelope>';

    protected function _before() {
        $this->testUser = new TestUser(10);
        $this->testUser2 = new TestUser(660);
    }

    protected function _after() {
        
    }

    protected function generatedMockXmlManualBet($xml) {
        $data = [
            '@attributes' => [
                'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
            ],
            's:Body' => [
                'ManuallyValidateBet' => [
                    '@attributes' => [
                        'xmlns' => 'http://mgsops.net/AdminAPI_Admin'
                    ],
                    '@value' => 1
                ]
            ]
        ];
        return Array2Xml::createXML('s:Envelope', $data)->saveXML();
    }

    protected function generatedMockXml($userArray, $type, $count = 1, $modify = false, $makeBet = false) {
        $tmpArray = array();
        foreach ($userArray as $key => $value) {
            for ($i = 0; $i < $count; $i++) {
                extract($value);
                if ($modify) {
                    $transactionNumber = $transactionNumber + 1;
                    $referenceNumber = $referenceNumber + 2;
                }
                if (isset($loginName)) {
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
        }
        if ($makeBet) {
            $this->madeBet($tmpArray);
        }
        if ($type == 'commit') {
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
        } else if ($type == 'rollback') {
            $data = [
                '@attributes' => [
                    'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
                ],
                's:Body' => [
                    'GetRollbackQueueDataResponse' => [
                        '@attributes' => [
                            'xmlns' => 'http://mgsops.net/AdminAPI_Admin'
                        ],
                        'GetRollbackQueueDataResult' => [
                            '@attributes' => [
                                'xmlns:a' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures',
                                'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance'
                            ],
                            'a:QueueDataResponse' => $tmpArray
                        ]
                    ]
                ]
            ];
        } else {
            $data = [];
        }
        return Array2Xml::createXML('s:Envelope', $data)->saveXML();
    }

    private function generateUniqId() {
        return mt_rand(100000, 999999);
    }

    private function madeBet(array $data) {
        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
            $transactionRequest = new TransactionRequest(
                    Config::get('integrations.microgaming.service_id'), $value['a:TransactionNumber'], $user->id, $user->getCurrency(), MicroGamingHelper::getTransactionDirection(TransactionRequest::TRANS_BET), TransactionHelper::amountCentsToWhole($value['a:ChangeAmount']), MicroGamingHelper::getTransactionType(TransactionRequest::TRANS_BET), MicroGamingObjectIdMap::generateHash($user_id, $value['a:TransactionCurrency'], $value['a:TransactionNumber']), $value['a:GameName']
            );

            $transactionHandler = new TransactionHandler($transactionRequest, $user);

            return $transactionHandler->handle(new ProcessMicroGaming());
        }
    }

    private function init($testData, $xmlMockB = '') {
        if (is_array($testData)) {
            $xmlMock = $this->generatedMockXml($testData, 'commit', 2, true, true);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedMockXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetCommitQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new CommitValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new CommitProcessor();
        return $obj;
    }

    private function initRollback($testData, $xmlMockB = '') {
        if (is_array($testData)) {
            $xmlMock = $this->generatedMockXml($testData, 'rollback', 2, true, true);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedMockXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetRollbackQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new RollbackValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new RollbackProcessor();
        return $obj;
    }

    private function operation($obj) {
        $response = new stdClass();
        $response->data = $obj->source->getData();
        $obj->validatorData->validateBaseStructure($response->data);
        $elements = $obj->validatorData->getData($response->data);
        $response->finishedDataWin = $obj->operationsProcessor->make($elements);
        $response->dataResponse = $obj->requestResolveData->getData($response->finishedDataWin);
        $obj->validationResolveData->validateBaseStructure($response->dataResponse);
        return $response;
    }

    // tests Commit


    public function testCommit() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $obj = $this->init($testData);

        $this->specify("Test correct commit", function() use($obj) {
            $response = $this->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zeroe", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testCommitDuplicate() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $obj = $this->init($testData);

        $this->specify("Test correct commit", function() use($obj) {
            $this->operation($obj);
            $response = $this->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be count two", $response->finishedDataWin)->count(2);
            verify("Must be equls true", $response->finishedDataWin[0]['isDuplicate'])->equals(1);
            verify("Response must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testException() {
        $testData = 'l>';
        $obj = $this->init($testData);

        $this->specify("Test incorrect data from sourse commit", function() use($obj) {
            $this->expectException(ParserException::class);
            $obj->source->getData();
        });
    }

    public function testException2() {
        $testData = '<xml><INCORRECT/></xml>';
        $obj = $this->init($testData);

        $this->specify("Test incorrect data from validator data commit", function() use($obj) {
            $this->expectException(Exception::class);
            $data = $obj->source->getData();
            $obj->validatorData->validateBaseStructure($data);
        });
    }

    public function testEmptyData() {
        $testData = [];
        $obj = $this->init($testData);

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
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $obj = $this->initRollback($testData);

        $this->specify("Test correct rollback", function() use($obj) {
            $response = $this->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
        });
    }

    public function testRollbackDuplicate() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $obj = $this->initRollback($testData);

        $this->specify("Test correct duplicate rollback", function() use($obj) {
            $response = $this->operation($obj);
            verify("Must be array", $response->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $response->finishedDataWin)->count(2);
            verify("Must be equls zero", $response->finishedDataWin[0]['isDuplicate'])->equals(0);
            verify("Resposne must be array", $response->dataResponse)->containsOnly('array');
            $responseDuplicate = $this->operation($obj);
            verify("Must be array", $responseDuplicate->finishedDataWin)->containsOnly('array');
            verify("Must be  count two", $responseDuplicate->finishedDataWin)->count(2);
            verify("Must be equls zero", $responseDuplicate->finishedDataWin[0]['isDuplicate'])->equals(1);
            verify("Resposne must be array", $responseDuplicate->dataResponse)->containsOnly('array');
        });
    }

}
