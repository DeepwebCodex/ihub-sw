<?php

namespace App\Http\Controllers\Api;

use App\Components\ExternalServices\AccountManager;
use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\CasinoTemplate;
use App\Http\Controllers\Api\Base\BaseApiController;
use Illuminate\Http\Request;

class CasinoController extends BaseApiController
{
    use MetaDataTrait;

    public static $exceptionTemplate = CasinoTemplate::class;

    public function __construct(XmlApiFormatter $formatter)
    {
        parent::__construct($formatter);

        //$this->middleware('check.json')->except('gen_token');
    }

    public function index(){

        $accountManager = new AccountManager();

        //exit(dump($accountManager->getOperations(null, AccountManager::DEPOSIT)));

        //exit(dump($accountManager->createTransaction(27, -6, 1452573, 1, AccountManager::RUB, AccountManager::DEPOSIT, 514100864, 'Commnet')));

        //exit(dump($accountManager->getCashDeskInfo(5)));
        //exit(dump($accountManager->getCashDeskInfo(3001)));
        exit(dump($accountManager->getPlayerInfoByPassportForSccs(83, 'xxx', 123)));
        exit(dump($accountManager->getPlayerInfoByCcidForSccs(83, 7000007)));

        return $this->respondOk(200, '', ['code' => $accountManager->getFreeCardId()]);
    }


}
