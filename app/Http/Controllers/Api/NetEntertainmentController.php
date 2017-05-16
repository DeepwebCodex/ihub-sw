<?php

namespace App\Http\Controllers\Api;

use App\Components\Integrations\Fundist\ApiValidation;
use App\Components\Integrations\Fundist\Balance;
use App\Components\Transactions\Strategies\NetEntertainment\ProcessNetEntertainment;
use App\Http\Requests\Fundist\BetRequest;
use App\Http\Requests\Fundist\WinRequest;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Http\Response;

/**
 * Class NetEntertainmentController
 * @package App\Http\Controllers\Api
 */
class NetEntertainmentController extends FundistController
{
    /**
     * @return string
     */
    protected function getIntegration()
    {
        return 'netEntertainment';
    }

    /**
     * @return string
     */
    protected function getObjectIdKey()
    {
        return 'i_gameid';
    }

    /**
     * @param $objectId
     * @return int
     */
    protected function getObjectId($objectId): int
    {
        return (int)$objectId;
    }

    /**
     * @param BetRequest $request
     * @return Response
     */
    public function bet(BetRequest $request)
    {
        $serviceId = $this->getOption('service_id');
        $user = IntegrationUser::get($this->userId, $serviceId, $this->integration);

        (new ApiValidation($request))
            ->checkTransactionParams(
                $serviceId,
                TransactionRequest::TRANS_BET,
                $this->partnerId
            )
            ->checkCurrency($user);

        $transactionRequest = new TransactionRequest(
            $serviceId,
            $this->getObjectId($request->input($this->objectIdKey)) . ':' . $request->input('i_actionid'),
            $user->id,
            $request->input('currency'),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_BET,
            $request->input('tid'),
            $this->gameId,
            $this->partnerId,
            $this->cashdeskId,
            app('GameSession')->get('userIp')
        );
        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new ProcessNetEntertainment());

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    /**
     * @param WinRequest $request
     * @return Response
     */
    public function win(WinRequest $request)
    {
        $serviceId = $this->getOption('service_id');
        $user = IntegrationUser::get($request->input('userId'), $serviceId, $this->integration);

        $transactionRequest = new TransactionRequest(
            $serviceId,
            $this->getObjectId($request->input($this->objectIdKey)),
            $user->id,
            $user->getCurrency(),
            TransactionRequest::D_DEPOSIT,
            (float)$request->input('amount'),
            TransactionRequest::TRANS_WIN,
            $request->input('tid'),
            $betTransaction->game_id,
            $betTransaction->partner_id,
            $betTransaction->cashdesk,
            $betTransaction->client_ip
        );

        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(new ProcessNetEntertainment());

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }
}
