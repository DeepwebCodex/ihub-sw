<?php

namespace Orion;

use ApiTester;
use App\Components\ExternalServices\MicroGaming\Orion\SoapEmulator;
use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Integrations\MicroGaming\Orion\CommitRollbackProcessor;
use App\Components\Integrations\MicroGaming\Orion\CompleteGameProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\GetFailedEndGameQueue;
use App\Components\Integrations\MicroGaming\Orion\Request\GetRollbackQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyCompleteGame;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Components\ThirdParty\Array2Xml;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGaming;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\EndGameValidation;
use App\Http\Requests\Validation\Orion\ManualCompleteValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use App\Http\Requests\Validation\Orion\RollbackValidation;
use App\Models\MicroGamingObjectIdMap;
use Carbon\Carbon;
use Codeception\Test\Unit;
use Exception;
use Illuminate\Support\Facades\Config;
use Mockery;
use stdClass;
use function env;

class TestData extends Unit
{

    public $sampleCommitQ = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetCommitQueueDataResponse xmlns="http://mgsops.net/AdminAPI_Admin"><GetCommitQueueDataResult xmlns:a="http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>6750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33740</a:RowId><a:TransactionNumber>4</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:21:50 PM</a:DateCreated><a:MgsReferenceNumber>9000033724</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33740</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>9950</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33748</a:RowId><a:TransactionNumber>6</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:30 PM</a:DateCreated><a:MgsReferenceNumber>9000033732</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33748</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33751</a:RowId><a:TransactionNumber>8</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:54 PM</a:DateCreated><a:MgsReferenceNumber>9000033735</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33751</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33758</a:RowId><a:TransactionNumber>11</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:25:46 PM</a:DateCreated><a:MgsReferenceNumber>9000033742</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33758</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33765</a:RowId><a:TransactionNumber>17</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:26:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033749</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33765</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33767</a:RowId><a:TransactionNumber>18</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:27:09 PM</a:DateCreated><a:MgsReferenceNumber>9000033751</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33767</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33776</a:RowId><a:TransactionNumber>22</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:26 PM</a:DateCreated><a:MgsReferenceNumber>9000033760</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33776</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33778</a:RowId><a:TransactionNumber>23</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:52 PM</a:DateCreated><a:MgsReferenceNumber>9000033762</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33778</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33784</a:RowId><a:TransactionNumber>28</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:35:58 PM</a:DateCreated><a:MgsReferenceNumber>9000033768</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33784</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33787</a:RowId><a:TransactionNumber>30</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:18 PM</a:DateCreated><a:MgsReferenceNumber>9000033771</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33787</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33792</a:RowId><a:TransactionNumber>33</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033776</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33792</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>250</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33799</a:RowId><a:TransactionNumber>36</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:38:13 PM</a:DateCreated><a:MgsReferenceNumber>9000033783</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33799</a:RowIdLong></a:QueueDataResponse></GetCommitQueueDataResult></GetCommitQueueDataResponse></s:Body></s:Envelope>';

    public function generatedXmlManualBet($xml)
    {
        $data = [
            '@attributes' => [
                'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
            ],
            's:Body' => [
                'ManuallyValidateBetResponse' => [
                    '@attributes' => [
                        'xmlns' => 'http://mgsops.net/AdminAPI_Admin'
                    ],
                    'ManuallyValidateBetResult' => true
                ]
            ]
        ];
        return Array2Xml::createXML('s:Envelope', $data)->saveXML();
    }

    public function generatedXml($userArray, $type, $count = 1, $modify = false,
            $makeBet = false)
    {
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

    public function generateUniqId()
    {
        return mt_rand(100000, 999999);
    }

    public function madeBet(array $data)
    {
        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
            $transactionRequest = new TransactionRequest(
                    Config::get('integrations.microgaming.service_id'), $value['a:TransactionNumber'], $user->id, $user->getCurrency(), MicroGamingHelper::getTransactionDirection(TransactionRequest::TRANS_BET), TransactionHelper::amountCentsToWhole($value['a:ChangeAmount']), MicroGamingHelper::getTransactionType(TransactionRequest::TRANS_BET), MicroGamingObjectIdMap::generateHash($user_id, $value['a:TransactionCurrency'], $value['a:TransactionNumber']), $value['a:GameName'], env('TEST_PARTNER_ID'), env('TEST_CASHEDESK')
            );

            $transactionHandler = new TransactionHandler($transactionRequest, $user);

            return $transactionHandler->handle(new ProcessMicroGaming());
        }
    }

    public function init($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $xmlMock = $this->generatedXml($testData, 'commit', 2, true, true);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetCommitQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new CommitValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new CommitRollbackProcessor('CommitQueue', TransactionRequest::TRANS_WIN);
        return $obj;
    }

    public function initRollback($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $xmlMock = $this->generatedXml($testData, 'rollback', 2, true, true);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetRollbackQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new RollbackValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new CommitRollbackProcessor('RollbackQueue', TransactionRequest::TRANS_REFUND);
        return $obj;
    }

    public function initRollbackWithoutBet($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $xmlMock = $this->generatedXml($testData, 'rollback', 1, true, false);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetRollbackQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new RollbackValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new CommitRollbackProcessor('RollbackQueue', TransactionRequest::TRANS_REFUND);
        return $obj;
    }

    public function createXmlEndGame()
    {
        $qEndGameData = [
            '@attributes' => [
                'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
            ],
            's:Body' => [
                'GetFailedEndGameQueueResponse' => [
                    '@attributes' => [
                        'xmlns' => 'http://mgsops.net/AdminAPI_Admin'
                    ],
                    'GetFailedEndGameQueueResult' => [
                        '@attributes' => [
                            'xmlns:a' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures',
                            'xmlns:i' => 'http://www.w3.org/2001/XMLSchema-instance'
                        ],
                        'a:GetFailedGamesResponse' => [
                            [
                                'a:ClientId' => 10002,
                                'a:Description' => '',
                                'a:ModuleId' => 12506,
                                'a:RowId' => -33753,
                                'a:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                                'a:SessionId' => 0,
                                'a:TimeCompleted' => '',
                                'a:TimeCreated' => '2016-12-12T13:25:05.193',
                                'a:TournamentId' => 0,
                                'a:TransNumber' => 9,
                                'a:UniqueId' => '10EUR',
                                'a:UserId' => 45443,
                            ],
                            [
                                'a:ClientId' => 10002,
                                'a:Description' => '',
                                'a:ModuleId' => 12506,
                                'a:RowId' => -23452,
                                'a:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                                'a:SessionId' => 0,
                                'a:TimeCompleted' => '',
                                'a:TimeCreated' => '2016-12-12T13:25:05.193',
                                'a:TournamentId' => 0,
                                'a:TransNumber' => 123,
                                'a:UniqueId' => '12EUR',
                                'a:UserId' => 325223,
                            ],
                        ]
                    ]
                ]
            ]
        ];

        $qManualCompleteData = [
            '@attributes' => [
                'xmlns:s' => 'http://schemas.xmlsoap.org/soap/envelope/',
            ],
            's:Body' => [
                'ManuallyCompleteGameResponse' => [
                    '@attributes' => [
                        'xmlns' => 'http://mgsops.net/AdminAPI_Admin',
                        'xmlns:a' => 'http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures',
                        'xmlns:i' => "http://www.w3.org/2001/XMLSchema-instance"
                    ],
                    'ManuallyCompleteGameResult' =>
                    ['a:CompleteGameResponse' => [
                            [
                                'a:RowId' => -33753,
                                'a:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                                'a:Success' => 1
                            ],
                            [
                                'a:RowId' => -23452,
                                'a:ServerId' => Config::get('integrations.microgamingOrion.serverId'),
                                'a:Success' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $ret = new stdClass();
        $ret->qEndGameData = Array2Xml::createXML('s:Envelope', $qEndGameData)->saveXML();
        $ret->qManualCompleteData = Array2Xml::createXML('s:Envelope', $qManualCompleteData)->saveXML();
        return $ret;
    }

    public function initEndGame()
    {

        $xml = $this->createXmlEndGame();


        $clientMockQ = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xml->qEndGameData));

        $clientManualVBMock = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xml->qManualCompleteData));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetFailedEndGameQueue($clientMockQ, $sourceProcessor);
        $obj->validatorData = new EndGameValidation();
        $obj->requestResolveData = new ManuallyCompleteGame($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualCompleteValidation();
        $obj->operationsProcessor = new CompleteGameProcessor();
        return $obj;
    }

    public function operation($obj)
    {
        $response = new stdClass();
        $response->data = $obj->source->getData();
        $obj->validatorData->validateBaseStructure($response->data);
        $elements = $obj->validatorData->getData($response->data);
        $response->finishedDataWin = $obj->operationsProcessor->make($elements);
        $response->dataResponse = $obj->requestResolveData->getData($response->finishedDataWin);
        $obj->validationResolveData->validateBaseStructure($response->dataResponse);
        return $response;
    }

    public function initCommitWithoutBet($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $xmlMock = $this->generatedXml($testData, 'commit', 1, true, false);
        } else {
            $xmlMock = $testData;
        }
        $clientMockQ = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientMockQ->method('sendRequest')->will($this->returnValue($xmlMock));

        if (!$xmlMockB) {
            $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        }
        $clientManualVBMock = $this->createMock(SoapEmulator::class, ['sendRequest']);
        $clientManualVBMock->method('sendRequest')->will($this->returnValue($xmlMockB));



        $obj = new stdClass();
        $sourceProcessor = new SourceProcessor();
        $obj->source = new GetCommitQueueData($clientMockQ, $sourceProcessor);
        $obj->validatorData = new CommitValidation();
        $obj->requestResolveData = new ManuallyValidateBet($clientManualVBMock, $sourceProcessor);
        $obj->validationResolveData = new ManualValidation();
        $obj->operationsProcessor = new CommitRollbackProcessor('CommitQueue', TransactionRequest::TRANS_WIN);
        return $obj;
    }

    public function initMock(ApiTester $I)
    {
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->andReturn('<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><s:Fault
><faultcode xmlns:a="http://schemas.microsoft.com/ws/2005/05/addressing/none">a:
ActionNotSupported</faultcode><faultstring xml:lang="en-US">The message with Act
ion "http://mgsops.net/AdminAPI_Admin/IVanguardAdmin2/GetFailedEndGameQueue" can
not be processed at the receiver, due to a ContractFilter mismatch at the Endpoi
ntDispatcher. This may be because of either a contract mismatch (mismatched Acti
ons between sender and receiver) or a binding/security mismatch between the send
er and the receiver.  Check that sender and receiver have the same contract and
the same binding (including security requirements, e.g. Message, Transport, None
).</faultstring></s:Fault></s:Body></s:Envelope>');
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

    public function initMock2(ApiTester $I)
    {
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->andThrow(new Exception('Ecxeption unknown'), 'Thrown exception');
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

}
