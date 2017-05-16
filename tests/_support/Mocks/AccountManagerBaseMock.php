<?php

namespace Testing;

use iHubGrid\Accounting\ExternalServices\AccountManager;
use Mockery;

class AccountManagerBaseMock
{
    private $balance;
    const SERVICE_IDS = [
        0, 1, 2, 3, 4, 6, 7, 8, 9, 10, 12, 13, 14, 16, 17, 20, 21, 22, 23,
        24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
        41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 53, 54, 107, 301
    ];

    const BET = 1;
    const WIN = 0;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function getMock()
    {
        /** @var Mockery\Mock $accountManager */
        $accountManager = Mockery::mock(AccountManager::class);

        $accountManager->shouldReceive('getUserInfo')
            ->withArgs([$this->params->userId])->andReturn(
                [
                    "id"            => $this->params->userId,
                    "wallets"       => [
                        [
                            "__record"  => "wallet",
                            "currency"  => $this->params->currency,
                            "is_active" => 1,
                            "deposit"   => $this->params->balance,
                        ],
                    ],
                    "user_services" => $this->getServices(),
                ]
            );

        $accountManager->shouldReceive('getFreeOperationId')->withNoArgs()->andReturn($this->getUniqueId());

        return $accountManager;
    }

    private function getUniqueId()
    {
        return round(microtime(true)) + mt_rand(1, 10000);
    }

    private function getServices()
    {
        return array_map(function($service_id){
            return [
                "__record"              => "user_service",
                "service_id"            => $service_id,
                "is_enabled"            => 1,
            ];
        }, self::SERVICE_IDS);
    }
}