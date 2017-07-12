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
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGamingOrion;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\EndGameValidation;
use App\Http\Requests\Validation\Orion\ManualCompleteValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use App\Http\Requests\Validation\Orion\RollbackValidation;
use App\Models\MicroGamingObjectIdMap;
use Carbon\Carbon;
use Codeception\Test\Unit;
use Exception;
use Helper\TestUser;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\ThirdParty\Array2Xml;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHelper;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Support\Facades\Config;
use MicroGaming\Helper;
use Mockery;
use stdClass;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;
use UnitTester;
use function env;

class TestData extends Unit
{

    /** @var Params  */
    public $params;
    public $currencyMg;
    public $sampleCommitQ = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetCommitQueueDataResponse xmlns="http://mgsops.net/AdminAPI_Admin"><GetCommitQueueDataResult xmlns:a="http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>6750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33740</a:RowId><a:TransactionNumber>4</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:21:50 PM</a:DateCreated><a:MgsReferenceNumber>9000033724</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33740</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>9950</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33748</a:RowId><a:TransactionNumber>6</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:30 PM</a:DateCreated><a:MgsReferenceNumber>9000033732</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33748</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33751</a:RowId><a:TransactionNumber>8</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:23:54 PM</a:DateCreated><a:MgsReferenceNumber>9000033735</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33751</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33758</a:RowId><a:TransactionNumber>11</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:25:46 PM</a:DateCreated><a:MgsReferenceNumber>9000033742</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33758</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33765</a:RowId><a:TransactionNumber>17</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:26:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033749</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33765</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33767</a:RowId><a:TransactionNumber>18</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:27:09 PM</a:DateCreated><a:MgsReferenceNumber>9000033751</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33767</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>100</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33776</a:RowId><a:TransactionNumber>22</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:26 PM</a:DateCreated><a:MgsReferenceNumber>9000033760</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33776</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33778</a:RowId><a:TransactionNumber>23</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:34:52 PM</a:DateCreated><a:MgsReferenceNumber>9000033762</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33778</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>1000</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33784</a:RowId><a:TransactionNumber>28</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:35:58 PM</a:DateCreated><a:MgsReferenceNumber>9000033768</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33784</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2500</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33787</a:RowId><a:TransactionNumber>30</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:18 PM</a:DateCreated><a:MgsReferenceNumber>9000033771</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33787</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>2750</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33792</a:RowId><a:TransactionNumber>33</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:36:41 PM</a:DateCreated><a:MgsReferenceNumber>9000033776</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33792</a:RowIdLong></a:QueueDataResponse><a:QueueDataResponse><a:LoginName>10EUR</a:LoginName><a:UserId>45443</a:UserId><a:ChangeAmount>250</a:ChangeAmount><a:TransactionCurrency>EUR</a:TransactionCurrency><a:Status>Unknown</a:Status><a:RowId>-33799</a:RowId><a:TransactionNumber>36</a:TransactionNumber><a:GameName>MGS_TombRaider</a:GameName><a:DateCreated>12/12/2016 15:38:13 PM</a:DateCreated><a:MgsReferenceNumber>9000033783</a:MgsReferenceNumber><a:ServerId>5034</a:ServerId><a:MgsPayoutReferenceNumber>0</a:MgsPayoutReferenceNumber><a:PayoutAmount>0</a:PayoutAmount><a:ProgressiveWin>false</a:ProgressiveWin><a:ProgressiveWinDesc i:nil="true"/><a:FreeGameOfferName/><a:TournamentId>0</a:TournamentId><a:Description i:nil="true"/><a:ExtInfo i:nil="true"/><a:RowIdLong>-33799</a:RowIdLong></a:QueueDataResponse></GetCommitQueueDataResult></GetCommitQueueDataResponse></s:Body></s:Envelope>';

    public function __construct(Params $params)
    {
        parent::__construct();
        $this->params = $params;
        $this->currencyMg = 'Euro';
    }

    private function createXmlEndGame()
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

    private function generatedXmlManualBet()
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

    private function createData($userArray, $count = 1, $modify = false): array
    {
        $tmpArray = array();
        foreach ($userArray as $value) {
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
                        'a:RowIdLong' => ($rowIdLong) ?? $rowId,
                    ];
                }
            }
        }

        return $tmpArray;
    }

    private function createXml(array $tmpArray, string $type): string
    {
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

    public function createAccountMock(array $data, $tester, bool $withBet = false)
    {
        $accountManagerMock = new AccountManagerMock($this->params);
        $helper = new Helper($this->params);
        foreach ($data as $value) {
            $balance = $this->params->getBalance();
            $objectId = $helper->getPreparedObjectId($value['a:TransactionNumber']);
            $accountManagerMock->userInfo()
                ->win($objectId, $value['a:ChangeAmount'] / 100, $balance - $value['a:ChangeAmount'] / 100 + $value['a:ChangeAmount'] / 100)
                ->mock($tester);
            if ($withBet) {
                $accountManagerMock->bet($objectId, $value['a:ChangeAmount'] / 100, $balance - $value['a:ChangeAmount'] / 100);
                $accountManagerMock->mock($tester);
                $user_id = (int) $value['a:LoginName'];
                $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
                $transactionRequest = new TransactionRequest(
                    Config::get('integrations.microgaming.service_id'), $value['a:TransactionNumber'], $user->id, $user->getCurrency(), MicroGamingHelper::getTransactionDirection(TransactionRequest::TRANS_BET), TransactionHelper::amountCentsToWhole($value['a:ChangeAmount']), MicroGamingHelper::getTransactionType(TransactionRequest::TRANS_BET), MicroGamingObjectIdMap::generateHash($user_id, $value['a:TransactionCurrency'], $value['a:TransactionNumber']), $value['a:GameName'], env('TEST_PARTNER_ID'), env('TEST_CASHEDESK')
                );

                $transactionHandler = new TransactionHandler($transactionRequest, $user);

                $transactionHandler->handle(new ProcessMicroGamingOrion($value));
            }
        }
    }

    public function createAccountMockWithException(UnitTester $tester)
    {
        $accountManagerMock = new AccountManagerMock($this->params);
        $accountManagerMock->get()->shouldReceive('createTransaction')
            ->andThrow(new ApiHttpException(404, '', ['code' => 6000, 'message' => 'Invalid operation order.']))->mock($tester);
    }

    public function generateUniqId()
    {
        return mt_rand(100000, 999999);
    }

    public function init($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $data = $this->createData($testData, 2, true);
            $xmlMock = $this->createXml($data, 'commit');
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
        $obj->data = $data ?? [];
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
            $data = $this->createData($testData, 2, true);
            $xmlMock = $this->createXml($data, 'rollback');
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
        $obj->data = $data ?? [];
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
            $data = $this->createData($testData, 1, true);
            $xmlMock = $this->createXml($data, 'rollback');
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

    public function initCommitWithoutBet($testData, $xmlMockB = '')
    {
        if (is_array($testData)) {
            $data = $this->createData($testData, 1, true);
            $xmlMock = $this->createXml($data, 'commit');
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

    public function initMockCommandFailedEndGame(ApiTester $I)
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

    public function initMockEndGameThrownException(ApiTester $I)
    {
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->andThrow(new Exception('Ecxeption unknown'), 'Thrown exception');
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

    public function initMockWhenLostRowId(ApiTester $I)
    {
        $testData[] = [
            'loginName' => $this->params->userId . $this->params->currency,
            'amount' => 111,
            'currency' => $this->currencyMg,
            'rowId' => $this->generateUniqId(),
            'rowIdLong' => 0,
            'transactionNumber' => $this->generateUniqId(),
            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $data = $this->createData($testData, 1);
        $xml = $this->createXml($data, 'commit');
        $xmlEndGame = $this->createXmlEndGame();
        $xmlMockB = $this->generatedXmlManualBet($xml);
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetCommitQueueData::class])->andReturn($xml);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyValidateBet::class])->andReturn($xmlMockB);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

    public function initMockEndGameWhenRowIdWithMinus(ApiTester $I)
    {
        $xml = $this->createXmlEndGame();
        $xml_GF = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><GetFailedEndGameQueueResponse xmlns="http://mgsops.net/AdminAPI_Admin"><GetFailedEndGameQueueResult xmlns:a="http://schemas.datacontract.org/2004/07/Orion.Contracts.VanguardAdmin.DataStructures" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><a:GetFailedGamesResponse><a:ClientId>10002</a:ClientId><a:Description i:nil="true"/><a:ModuleId>12856</a:ModuleId><a:RowId>-6552318455</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:12:11.197</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>29357</a:TransNumber><a:UniqueId>1490523UAH</a:UniqueId><a:UserId>5319901</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>93</a:ModuleId><a:RowId>-6552334232</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:14:01.26</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>159</a:TransNumber><a:UniqueId>1722374UAH</a:UniqueId><a:UserId>6304721</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10002</a:ClientId><a:Description i:nil="true"/><a:ModuleId>12856</a:ModuleId><a:RowId>-6552334377</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:14:02.097</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>29378</a:TransNumber><a:UniqueId>1490523UAH</a:UniqueId><a:UserId>5319901</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>93</a:ModuleId><a:RowId>-6552347934</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:15:36.903</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>161</a:TransNumber><a:UniqueId>1722374UAH</a:UniqueId><a:UserId>6304721</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10006</a:ClientId><a:Description i:nil="true"/><a:ModuleId>2</a:ModuleId><a:RowId>-6552354424</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:16:22.313</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>437</a:TransNumber><a:UniqueId>1706628RUB</a:UniqueId><a:UserId>6212966</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>10231</a:ModuleId><a:RowId>-6552354481</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:16:22.673</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>4525</a:TransNumber><a:UniqueId>1673575UAH</a:UniqueId><a:UserId>6016530</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>10353</a:ModuleId><a:RowId>-6552455289</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:27:39.263</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>44278</a:TransNumber><a:UniqueId>1682703UAH</a:UniqueId><a:UserId>6060237</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>10347</a:ModuleId><a:RowId>-6552455446</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:27:39.913</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>6931</a:TransNumber><a:UniqueId>1683496UAH</a:UniqueId><a:UserId>6245745</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>82</a:ModuleId><a:RowId>-6552489629</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:31:29.523</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>19156</a:TransNumber><a:UniqueId>1663083UAH</a:UniqueId><a:UserId>5963772</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>10001</a:ClientId><a:Description i:nil="true"/><a:ModuleId>12512</a:ModuleId><a:RowId>-6552537757</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:36:24.693</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>55845</a:TransNumber><a:UniqueId>1472167UAH</a:UniqueId><a:UserId>5905752</a:UserId></a:GetFailedGamesResponse><a:GetFailedGamesResponse><a:ClientId>40302</a:ClientId><a:Description i:nil="true"/><a:ModuleId>11091</a:ModuleId><a:RowId>-6552598447</a:RowId><a:ServerId>1115</a:ServerId><a:SessionId>0</a:SessionId><a:TimeCompleted i:nil="true"/><a:TimeCreated>2017-03-29T09:42:31.977</a:TimeCreated><a:TournamentId>0</a:TournamentId><a:TransNumber>174912</a:TransNumber><a:UniqueId>1439559UAH</a:UniqueId><a:UserId>4757041</a:UserId></a:GetFailedGamesResponse></GetFailedEndGameQueueResult></GetFailedEndGameQueueResponse></s:Body></s:Envelope>';
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetFailedEndGameQueue::class])->andReturn($xml_GF);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyCompleteGame::class])->andReturn($xml->qManualCompleteData);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

    public function initMockCommitWithoutBet(ApiTester $I)
    {

        $testData[] = [
            'loginName' => $this->params->userId . $this->params->currency,
            'amount' => 111,
            'currency' => $this->currencyMg,
            'rowId' => 0,
            'rowIdLong' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(),
            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $data = $this->createData($testData, 10);
        $xml = $this->createXml($data, 'commit');
        $xmlMockB = $this->generatedXmlManualBet($xml);
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetCommitQueueData::class])->andReturn($xml);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyValidateBet::class])->andReturn($xmlMockB);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }

    public function initCommit(ApiTester $I)
    {
        $testData[] = [
            'loginName' => $this->params->userId . $this->params->currency,
            'amount' => 111,
            'currency' => $this->currencyMg,
            'rowId' => 0,
            'rowIdLong' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(),
            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $data = $this->createData($testData, 2, true);
        $xmlMock = $this->createXml($data, 'commit');
        $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetCommitQueueData::class])->andReturn($xmlMock);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyValidateBet::class])->andReturn($xmlMockB);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
        return $data;
    }

    public function initRollbackApi(ApiTester $I)
    {
        $testData[] = [
            'loginName' => $this->params->userId . $this->params->currency,
            'amount' => 111,
            'currency' => $this->currencyMg,
            'rowId' => 0,
            'rowIdLong' => $this->generateUniqId(),
            'transactionNumber' => $this->generateUniqId(),
            'serverId' => Config::get('integrations.microgamingOrion.serverId'),
            'referenceNumber' => $this->generateUniqId()
        ];
        $data = $this->createData($testData, 2, true);
        $xmlMock = $this->createXml($data, 'rollback');
        $xmlMockB = $this->generatedXmlManualBet($xmlMock);
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetRollbackQueueData::class])->andReturn($xmlMock);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyValidateBet::class])->andReturn($xmlMockB);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
        return $data;
    }

    public function initEndGameApi(ApiTester $I)
    {
         $xml = $this->createXmlEndGame();
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->withArgs([GetFailedEndGameQueue::class])->andReturn($xml->qEndGameData);
        $mock->shouldReceive('sendRequest')->withArgs([ManuallyCompleteGame::class])->andReturn($xml->qManualCompleteData);
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }
}
