<?php

namespace App\Http\Controllers\Api;

use App\Components\Formatters\WirexGamingApiFormatter;
use App\Components\Integrations\GameSession\Exceptions\SessionDoesNotExist;
use App\Components\Integrations\WirexGaming\CodeMapping;
use App\Components\Integrations\WirexGaming\WirexGamingHelper;
use App\Components\Traits\MetaDataTrait;
use App\Components\Transactions\TransactionRequest;
use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\WirexGamingTemplate;
use App\Http\WirexGaming\AddDepositRequest;
use App\Http\WirexGaming\AddWithdrawRequest;
use App\Http\WirexGaming\AvailableBalanceRequest;
use App\Http\WirexGaming\CancelTransactionRequest;
use App\Http\WirexGaming\GetPersistentSessionRequest;
use App\Http\WirexGaming\GetUserDataRequest;
use App\Http\WirexGaming\RollbackWithdrawRequest;
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
     * BetGamesController constructor.
     * @param WirexGamingApiFormatter $formatter
     */
    public function __construct(WirexGamingApiFormatter $formatter)
    {
        parent::__construct($formatter);

        $this->options = config('integrations.wirexGaming');

        $this->middleware('check.ip:wirexGaming');
        //$this->middleware('input.xml');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $body = $request->input('soap:Body');
        $method = key($body);

        if (method_exists($this, $method)) {
            $data = $body[$method];
            $this->data = $data;
            return app()->call([$this, $method], $request->all());
        }

        return app()->call([$this, 'error'], $request->all());
    }

    /**
     * @param Request $request
     */
    public function docs(Request $request)
    {
        $input = $request->input();
        $exampleUrl = 'https://pcws.casino.com:443/portal/PlatformControllerWS';
        $url = 'http://vb-test.favbet.com/ihub/wirex';
        $wsdlFolder = public_path() . '/soap/wirex';
        if (isset($input['wsdl'])) {
            $content = file_get_contents($wsdlFolder . '/wirex.wsdl');
            $content = str_replace($exampleUrl, $url, $content);
        } elseif (isset($input['xsd'])) {
            if ($input['xsd'] == 1) {
                $content = file_get_contents($wsdlFolder . '/schema1.xsd');
                $content = str_replace($exampleUrl, $url, $content);
            } elseif ($input['xsd'] == 2) {
                $content = file_get_contents($wsdlFolder . '/schema2.xsd');
            }
        }
        $header['Content-Type'] = 'text/xml; charset=utf-8';
        return ResponseFacade::make($content, 200, $header);
    }

    /**
     * @param GetPersistentSessionRequest $request
     * @return Response
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function getPersistentSession(GetPersistentSessionRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);

        $sessionId = $request->input('remotePersistentSessionId');
        $sessionMagic = $request->input('remotePersistentSessionMagic');

        $sessionContext = [$sessionId, $sessionMagic];

        try {
            $sessionToken = \app('GameSession')->getSessionIdByContext($sessionContext);
        } catch (SessionDoesNotExist $exception) {
            throw new ApiHttpException(
                400,
                null,
                CodeMapping::getByErrorCode($exception->getCode())
            );
        }

        return $this->respondOk(200, null, [
            'sessionToken' => $sessionToken
        ]);
    }

    /**
     * @param GetUserDataRequest $request
     * @return Response
     */
    public function getUserData(GetUserDataRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        return $this->respondOk(200, '', [
            'address' => $user->adress,
            'birthcountry' => '',
            'birthdate' => $user->date_of_birth,
            'birthstate' => '',
            'city' => $user->city,
            'currency' => $user->getCurrency(),
            'email' => $user->email,
            'firstname' => $user->first_name,
            'language' => '',
            'lastname' => $user->last_name,
            'partyTypes' => '',
            'phone' => $user->phone_number,
            'sex' => '',
            'userType' => '',
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
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        return $this->respondOk(200, null, [
            'balance' => $user->getBalanceInCents(),
            'currency' => $user->getCurrency()
        ]);
    }

    /**
     * @param AddWithdrawRequest $request
     * @return Response
     */
    public function addWithdrawEntry(AddWithdrawRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        WirexGamingHelper::checkSessionCurrency($user->getCurrency());

        $transactionUid = $request->input('transactionUid');
        $transactionId = WirexGamingHelper::parseUid($transactionUid);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            0,
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_BET,
            $transactionId,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'accountEntryDetailed' => [
                'accountEntry' => [
                    'account' => '',
                    'accountEntryType' => '',
                    'amount' => $request->input('amount'),
                    'balance' => $transactionResponse->getBalanceInCents(),
                    'codice' => '',
                    'creationDate' => '',
                    'currency' => '',
                    'description' => '',
                    'extraCodice' => '',
                    'valuta' => '',
                ],
                'selectedAmounts' => '',
                'selectedEntryTypeLabels' => '',
            ],
            'callerContextId' => '',
            'contextId' => '',
            'customerId' => '',
            'sourceContextId' => '',
        ]);
    }

    /**
     * @param RollbackWithdrawRequest $request
     * @return Response
     */
    public function rollbackWithdraw(RollbackWithdrawRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        WirexGamingHelper::checkSessionCurrency($user->getCurrency());

        $transactionUid = $request->input('transactionUid');
        $transactionId = WirexGamingHelper::parseUid($transactionUid);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            0,
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            $request->input('amount'),
            TransactionRequest::TRANS_REFUND,
            $transactionId,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'relatedTransUid' => $transactionResponse->operation_id,
            'sessionToken' => $request->input('sessionToken'),
        ]);
    }

    /**
     * @param AddDepositRequest $request
     * @return Response
     */
    public function addDepositEntry(AddDepositRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        WirexGamingHelper::checkSessionCurrency($user->getCurrency());

        $transactionUid = $request->input('transactionUid');
        $transactionId = WirexGamingHelper::parseUid($transactionUid);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            0,
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            $request->input('amount'),
            TransactionRequest::TRANS_WIN,
            $transactionId,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'accountEntryDetailed' => [
                'accountEntry' => [
                    'account' => '',
                    'accountEntryType' => '',
                    'amount' => $request->input('amount'),
                    'balance' => $transactionResponse->getBalanceInCents(),
                    'codice' => '',
                    'creationDate' => '',
                    'currency' => '',
                    'description' => '',
                    'extraCodice' => '',
                    'valuta' => '',
                ],
                'selectedAmounts' => '',
                'selectedEntryTypeLabels' => '',
            ],
            'callerContextId' => '',
            'contextId' => '',
            'customerId' => '',
            'sourceContextId' => '',
        ]);
    }

    /**
     * @param CancelTransactionRequest $request
     * @return Response
     */
    public function cancelTransaction(CancelTransactionRequest $request)
    {
        $userUid = $request->input('partyOriginatingUid');
        $userId = WirexGamingHelper::parseUid($userUid);
        $user = IntegrationUser::get($userId, $this->getOption('service_id'), 'wirexGaming');

        $sessionToken = $request->input('sessionToken');

        WirexGamingHelper::checkSessionCurrency($user->getCurrency());

        $transactionUid = $request->input('transactionUid');
        $transactionId = WirexGamingHelper::parseUid($transactionUid);

        $transactionRequest = new TransactionRequest(
            $this->getOption('service_id'),
            0,
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_REFUND,
            $transactionId,
            \app('GameSession')->get('game_id'),
            \app('GameSession')->get('partner_id'),
            \app('GameSession')->get('cashdesk_id'),
            \app('GameSession')->get('userIp')
        );

        $transactionResponse = WirexGamingHelper::handleTransaction($transactionRequest, $user);

        return $this->respondOk(200, null, [
            'relatedTransUid' => $transactionResponse->operation_id,
            'sessionToken' => $request->input('sessionToken'),
        ]);
    }
}
