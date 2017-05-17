<?php


namespace Testing\DriveMediaAristocrat;

use App\Models\DriveMediaAristocratProdObjectIdMap;
use iHubGrid\Accounting\Users\IntegrationUser;

class Params
{
    public $enableMock;

    public $amount = 0.05;
    public $winLose = -0.05;
    public $currency = 'EUR';
    public $balance = 100.34;

    public $tradeId = '111111_99999';

    public $bet_operation_id = 9543958;
    public $win_operation_id = 2834034;

    public $userId;
    public $login;
    public $wrongUserId = 234234565465465454;
    public $cashdeskId;
    public $partnerId;
    public $userIP = "127.0.0.1";
    public $action = '/aristocrat';


    public function __construct()
    {
        $this->enableMock = env('ACCOUNT_MANAGER_MOCK_IS_ENABLED') ?? true;

        $this->login = (int)env('TEST_USER_ID') . '--1---5--127-0-0-1';
        $this->userId = (int)env('TEST_USER_ID');
        $this->cashdeskId = (int)env('TEST_CASHEDESK');
        $this->partnerId = (int)env('TEST_PARTNER_ID');
        $this->serviceId = (int)config('integrations.DriveMediaAristocrat.service_id');
        $this->options = config('integrations.gameart');
        $this->object_id = DriveMediaAristocratProdObjectIdMap::getObjectId($this->tradeId);
    }

    public function getTradeId()
    {
        if ($this->enableMock) {
            return $this->tradeId;
        }

        return (string)rand(1111111111111,9999999999999).'_'.rand(111111111,999999999);
    }


    public function getBalance()
    {
        if ($this->enableMock) {
            return $this->balance;
        }

        return IntegrationUser::get($this->userId, 0, 'tests')->getBalance();
    }
}