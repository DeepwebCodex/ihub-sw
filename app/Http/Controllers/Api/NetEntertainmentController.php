<?php

namespace App\Http\Controllers\Api;

use App\Components\Integrations\Fundist\ApiValidation;
use App\Components\Integrations\Fundist\Balance;
use App\Components\Integrations\Fundist\CodeMapping;
use App\Components\Integrations\Fundist\StatusCode;
use App\Components\Transactions\Strategies\NetEntertainment\ProcessNetEntertainment;
use App\Http\Requests\Fundist\BetRequest;
use App\Http\Requests\Fundist\WinRequest;
use App\Models\NetEntertainmentObjectIdMap;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
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

        //KOLOK: object_id и foreign_id поменяны местами, чтобы прокинуть i_actionid
        $transactionRequest = new TransactionRequest(
            $serviceId,
            (int)$request->input('tid'),
            $user->id,
            $request->input('currency'),
            TransactionRequest::D_WITHDRAWAL,
            $request->input('amount'),
            TransactionRequest::TRANS_BET,
            $this->getObjectId($request->input($this->objectIdKey)) . ':' . $request->input('i_actionid'),
            $this->gameId,
            $this->partnerId,
            $this->cashdeskId,
            app('GameSession')->get('userIp')
        );
        $transaction = new TransactionHandler($transactionRequest, $user);
        $response = $transaction->handle(app(ProcessNetEntertainment::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }

    /**
     * @param WinRequest $request
     * @return Response
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException
     */
    public function win(WinRequest $request)
    {
        $serviceId = $this->getOption('service_id');
        $user = IntegrationUser::get($request->input('userId'), $serviceId, $this->integration);

        $gameId = $this->getObjectId($request->input($this->objectIdKey));
        $betObjectId = NetEntertainmentObjectIdMap::findObjectIdByGameId($gameId);
        if (!$betObjectId) {
            throw new ApiHttpException(
                Response::HTTP_OK,
                null,
                CodeMapping::getByErrorCode(StatusCode::BAD_OPERATION_ORDER)
            );
        }
        $betTransaction = Transactions::getBetTransaction(
            $serviceId,
            $user->id,
            $betObjectId
        );
        if (!$betTransaction) {
            throw new ApiHttpException(
                Response::HTTP_OK,
                null,
                CodeMapping::getByErrorCode(StatusCode::BAD_OPERATION_ORDER)
            );
        }
        if ($betTransaction->user_id != $user->id
            || $betTransaction->currency != $request->input('currency')
        ) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::TRANSACTION_MISMATCH,
            ]);
        }

        $transactionRequest = new TransactionRequest(
            $serviceId,
            $betTransaction->object_id,
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
        $response = $transaction->handle(app(ProcessNetEntertainment::class));

        return $this->responseOk([
            'tid' => $request->input('tid'),
            'balance' => Balance::toFloat($response->getBalanceInCents())
        ]);
    }
}
