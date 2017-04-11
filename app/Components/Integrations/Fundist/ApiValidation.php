<?php


namespace App\Components\Integrations\Fundist;


use App\Components\Users\IntegrationUser;
use App\Exceptions\Api\ApiHttpException;
use App\Http\Requests\Fundist\BaseRequest;
use App\Models\Transactions;
use Symfony\Component\HttpFoundation\Response;

class ApiValidation
{
    private $request;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    public function checkTransactionParams($serviceId, $transactionType, $partnerId)
    {
        $result = Transactions::getTransaction($serviceId, $this->request->input('tid'), $transactionType, $partnerId);
        if(!$result){
            return $this;
        }
        $trans = $result->getAttributes();

        if ($trans['user_id'] != $this->request->input('userid')
            || $trans['currency'] != $this->request->input('currency')
            || $trans['amount'] != $this->request->input('amount') * 100
        ) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::TRANSACTION_MISMATCH,
            ]);
        }

        return $this;
    }

    public function checkCurrency(IntegrationUser $user)
    {
        if ($user->getCurrency() != $this->request->input('currency')) {
            throw new ApiHttpException(Response::HTTP_OK, null, [
                'code' => StatusCode::CURRENCY,
            ]);
        }

        return $this;
    }
}