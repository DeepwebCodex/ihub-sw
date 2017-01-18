<?php

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Integrations\MicroGaming\Orion\OperationsProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Components\ThirdParty\Array2Xml;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use App\Models\MicroGamingObjectIdMap;
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

    protected function generatedMockXml($userArray, $count = 1, $modify = false, $makeBet = false) {
        $tmpArray = array();
        foreach ($userArray as $key => $value) {
            for ($i = 0; $i < $count; $i++) {
                extract($value);
                if ($modify) {
                    $transactionNumber = $transactionNumber + 1;
                    $referenceNumber = $referenceNumber + 2;
                }
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
        if ($makeBet) {
            $this->madeBet($tmpArray);
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

    // tests
    public function estGetCommitQueueData() {

        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());
        $this->specify("Test get commit QueueData", function() use($commit) {
            $t = $commit->getData();
            verify("Must be fill", $t)->notEmpty();
            verify("Must be array", $t)->containsOnly('array');
        });


        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue('wrong xml'));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());
        $this->specify("Test commit QueueData when source empty", function() use($commit) {
            $this->expectException(ParserException::class);
            $t = $commit->getData();
        });
    }

    public function estGetCommitQueueDataWithSample() {
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($this->sampleCommitQ));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());
        $this->specify("Test get commit QueueData with sample", function() use($commit) {
            $t = $commit->getData();
            verify("Must be fill", $t)->notEmpty();
            verify("Must be array", $t)->containsOnly('array');
            $tmpArray = CommitValidation::getData($t);
            verify("Count must be equal 12", $tmpArray)->count(12);
        });
    }

    public function estValidationCommitData() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $this->specify("Test validation commit data", function() use($data, $validatorCommitData) {
            verify("Validation passed", $validatorCommitData->validateBaseStructure($data))->true();
        });
    }

    public function estProcessTransaction() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $validatorCommitData->validateBaseStructure($data);

        $this->specify("Test absent bet", function() use($data) {
            $handleCommitRes = OperationsProcessor::commit($data);
            verify("Must be array", $handleCommitRes)->containsOnly('array');
            verify("Must be equls zero", $handleCommitRes)->count(0);
        });

        $xmlMock = $this->generatedMockXml($testData, 1, true, true);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $validatorCommitData->validateBaseStructure($data);

        $this->specify("Test valid bet = commit", function() use($data) {
            $handleCommitRes = OperationsProcessor::commit($data);
            verify("Must be array", $handleCommitRes)->containsOnly('array');
            verify("Must be equls count one", $handleCommitRes)->count(1);
        });
    }

    public function testOperationManualBet() {
        $testData[] = [
            'loginName' => $this->testUser->getUser()->id . $this->testUser->getCurrency(),
            'amount' => 111, 'currency' => $this->testUser->getCurrency(), 'rowId' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(), 'serverId' => config('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $xmlMock = $this->generatedMockXml($testData, 2, true, true);
        $clientMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $commit = new GetCommitQueueData($clientMock, new SourceProcessor());

        $data = $commit->getData();
        $validatorCommitData = new CommitValidation();
        $validatorCommitData->validateBaseStructure($data);
        $handleCommitRes = OperationsProcessor::commit($data);

        $xmlMock = $this->generatedMockXmlManualBet($xmlMock);
        $clientManualVBMock = $this->createMock(SoapEmul::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMock));
        $manualValidateBet = new ManuallyValidateBet($clientManualVBMock, new SourceProcessor());

        $this->specify("Test manual bet", function() use($handleCommitRes, $manualValidateBet) {
            $dataResponse = $manualValidateBet->getData($handleCommitRes);
            $mBetValidation = new ManualValidation();
            $mBetValidation->validateBaseStructure($dataResponse);
            verify("Must be array", $handleCommitRes)->containsOnly('array');
            verify("Must be equls count one", $handleCommitRes)->count(2);
        });
    }

}
