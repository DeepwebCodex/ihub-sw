<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\WirexGamingApiFormatter;
use iHubGrid\SeamlessWalletCore\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\WirexGaming\CodeMapping;
use App\Components\Integrations\WirexGaming\WirexGamingHelper;
use App\Exceptions\Api\Templates\WirexGamingTemplate;
use App\Http\WirexGaming\AddDepositRequest;
use App\Http\WirexGaming\AddWithdrawRequest;
use App\Http\WirexGaming\AvailableBalanceRequest;
use App\Http\WirexGaming\CancelTransactionRequest;
use App\Http\WirexGaming\GetPersistentSessionRequest;
use App\Http\WirexGaming\GetUserDataRequest;
use App\Http\WirexGaming\RollbackWithdrawRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\ErrorHandler\Http\Controllers\Api\BaseApiController;
use iHubGrid\ErrorHandler\Http\Traits\MetaDataTrait;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

/**
 * Class WirexController
 * @package App\Http\Controllers\Api
 */
class WirexGamingController extends BaseApiController
{
    use MetaDataTrait;

    protected $data;

    public static $exceptionTemplate = WirexGamingTemplate::class;

    /**
     * WirexGamingController constructor.
     * @param WirexGamingApiFormatter $formatter
     */
    public function __construct(WirexGamingApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.wirexGaming');

        $this->middleware('check.ip:wirexGaming');
    }

    /**
     * @param $method
     * @return mixed|string
     */
    protected function mapRequestDataField($method)
    {
        $defaultValue = 'request';
        $dataFieldMapping = [
            'addDepositEntry' => 'accountEntryPlatformRequest',
            'addWithdrawEntry' => 'accountEntryPlatformRequest',
            'cancelTransaction' => 'accountEntryPlatformRequest',
            'rollBackWithdraw' => 'transactionRequest',
        ];
        if (array_key_exists($method, $dataFieldMapping)) {
            return $dataFieldMapping[$method];
        }
        return $defaultValue;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $body = $request->input('S:Body');
        $method = key($body);
        $method = str_replace('ns2:', '', $method);

        $this->addMetaField('method', $method);

        if (method_exists($this, $method)) {
            $data = $body['ns2:' . $method][$this->mapRequestDataField($method)];
            $this->data = $data;
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    /**
     * @param Request $request
     */
    public function wsdl(Request $request)
    {
        $input = $request->input();
        $exampleUrl = 'https://pcws.casino.com:443/portal/PlatformControllerWS';
        $url = array_get($this->options, 'wsdl_url');
        $wsdlFolder = public_path() . '/soap/wirex';
        if (isset($input['wsdl'])) {
            $content = file_get_contents($wsdlFolder . '/wirex.wsdl');
            $content = str_replace($exampleUrl, $url, $content);
        } elseif (isset($input['xsd'])) {
            $xsdNumber = (int)$input['xsd'];
            if ($xsdNumber === 1) {
                $content = file_get_contents($wsdlFolder . '/schema1.xsd');
                $content = str_replace($exampleUrl, $url, $content);
            } elseif ($xsdNumber === 2) {
                $content = file_get_contents($wsdlFolder . '/schema2.xsd');
            }
        }
        if (empty($content)) {
            $this->error();
        }
        $header['Content-Type'] = 'text/xml; charset=utf-8';
        return ResponseFacade::make($content, 200, $header);
    }

    public function error()
    {
        throw new ApiHttpException(
            404,
            'Unknown method',
            CodeMapping::getByMeaning(CodeMapping::UNKNOWN_METHOD)
        );
    }

    /**
     * @param GetPersistentSessionRequest $request
     * @return Response
     */
    public function getPersistentSession(GetPersistentSessionRequest $request)
    {
        $sessionId = $this->data['remotePersistentSessionId'];
        $sessionMagic = $this->data['remotePersistentSessionMagic'];

        $sessionContext = [$sessionId, $sessionMagic];
        try {
            $sessionToken = \app('GameSession')->getSessionIdByContext($sessionContext, 'md5');
        } catch (SessionDoesNotExist $exception) {
            throw new ApiHttpException(
                400,
                null,
                CodeMapping::getByErrorCode($exception->getCode())
            );
        }
        return $this->respondOk(200, '', [
            'sessionToken' => $sessionToken
        ]);
    }

    /**
     * @param GetUserDataRequest $request
     * @return Response
     */
    public function getUserData(GetUserDataRequest $request)
    {
        $userUid = $this->data['partyOriginatingUid'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        return $this->respondOk(200, '', [
            'address' => $user->adress,
            'birthcountry' => '',
            'birthdate' => date('Y-m-d', strtotime($user->date_of_birth)),
            'birthstate' => '',
            'city' => $user->city,
            'currency' => $user->getCurrency(),
            'email' => $user->email,
            'firstname' => $user->first_name,
            'language' => $user->lang,
            'lastname' => $user->last_name,
            'partyTypes' => 2,
            'phone' => $user->phone_number,
            'sex' => '',
            'userType' => 0,
            'username' => $user->login,
            'zip' => $user->zip,
        ]);
    }

    /**
     * @param AvailableBalanceRequest $request
     * @return Response
     */
    public function getAvailableBalance(AvailableBalanceRequest $request)
    {
        $userUid = $this->data['partyOriginatingUId'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        return $this->respondOk(200, '', [
            'balance' => $user->getBalance(),
            'currency' => $user->getCurrency()
        ]);
    }

    /**
     * @param AddWithdrawRequest $request
     * @return Response
     */
    public function addWithdrawEntry(AddWithdrawRequest $request)
    {
        $userUid = $this->data['partyOriginatingUid'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        $requestCurrency = array_get($this->data, 'accountEntryDetailed.accountEntry.currency');
        WirexGamingHelper::checkRequestCurrency($user->getCurrency(), $requestCurrency);

        $transactionUid = $this->data['transactionUid'];

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $transactionUid,
            $user->id,
            $requestCurrency,
            TransactionRequest::D_WITHDRAWAL,
            array_get($this->data, 'accountEntryDetailed.accountEntry.amount'),
            TransactionRequest::TRANS_BET,
            $transactionUid,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, '', [
            'accountEntryDetailed' => [
                'accountEntry' => array_merge(
                    array_get($this->data, 'accountEntryDetailed.accountEntry'),
                    [
                        'balance' => $transactionResponse->getBalance(),
                        'codice' => 0,
                        'description' => '',
                        'valuta' => '',
                    ]
                ),
            ],
            'callerContextId' => $this->data['callerContextId'],
            'contextId' => $this->data['contextId'],
            'sourceContextId' => $this->data['sourceContextId'],
        ]);
    }

    /**
     * @param RollbackWithdrawRequest $request
     * @return Response
     */
    public function rollbackWithdraw(RollbackWithdrawRequest $request)
    {
        $userUid = $this->data['partyOriginatingUid'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        $betTransaction = Transactions::getBetTransaction(
            $this->getOption('service_id'),
            $user->id,
            $this->data['relatedTransUid']
        );
        if (null === $betTransaction) {
            return $this->respondOk(200, '', [
                'relatedTransUid' => $this->data['relatedTransUid'],
            ]);
        }

        $transactionUid = $this->data['transactionUid'];

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $this->data['relatedTransUid'],
            $user->id,
            $betTransaction->currency,
            TransactionRequest::D_DEPOSIT,
            $this->data['amount'],
            TransactionRequest::TRANS_REFUND,
            $transactionUid,
            $betTransaction->game_id,
            $betTransaction->partner_id,
            $betTransaction->cashdesk,
            $betTransaction->client_ip
        );

        WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, '', [
            'relatedTransUid' => $this->data['relatedTransUid'],
        ]);
    }

    /**
     * @param AddDepositRequest $request
     * @return Response
     */
    public function addDepositEntry(AddDepositRequest $request)
    {
        $userUid = $this->data['partyOriginatingUid'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        $requestCurrency = array_get($this->data, 'accountEntryDetailed.accountEntry.currency');
        WirexGamingHelper::checkRequestCurrency($user->getCurrency(), $requestCurrency);

        $transactionUid = $this->data['transactionUid'];

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $this->data['relatedTransUid'],
            $user->id,
            $requestCurrency,
            TransactionRequest::D_DEPOSIT,
            array_get($this->data, 'accountEntryDetailed.accountEntry.amount'),
            TransactionRequest::TRANS_WIN,
            $transactionUid,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, '', [
            'accountEntryDetailed' => [
                'accountEntry' => array_merge(
                    array_get($this->data, 'accountEntryDetailed.accountEntry'),
                    [
                        'balance' => $transactionResponse->getBalance(),
                        'codice' => 0,
                        'description' => '',
                        'valuta' => '',
                    ]
                ),
            ],
            'callerContextId' => $this->data['callerContextId'],
            'contextId' => $this->data['contextId'],
            'sourceContextId' => $this->data['sourceContextId'],
        ]);
    }

    /**
     * @param CancelTransactionRequest $request
     * @return Response
     */
    /*public function cancelTransaction(CancelTransactionRequest $request)
    {
        $userUid = $this->data['partyOriginatingUid'];
        $userId = WirexGamingHelper::parseUid($this->data['serverPid'], $userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        $transactionUid = $this->data['transactionUid'];

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            $this->data['relatedTransUid'],
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            $this->data['amount'],
            TransactionRequest::TRANS_REFUND,
            $transactionUid,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, '', [
            'relatedTransUid' => $this->data['relatedTransUid'],
        ]);
    }*/
}
