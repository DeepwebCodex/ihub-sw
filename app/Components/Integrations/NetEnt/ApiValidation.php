<?php


namespace App\Components\Integrations\NetEnt;


use App\Http\Requests\NetEnt\BaseRequest;
use App\Models\Transactions;

class ApiValidation
{
    private $request;

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }

    public function checkTransactionParams($service_id, $transaction_type, $partner_id)
    {
        $result = Transactions::getTransaction($service_id, $this->request->input('tid'), $transaction_type, $partner_id);
        if(!$result){
            return true;
        }
        $trans = $result->getAttributes();

        return $trans['user_id'] == $this->request->input('userid')
            && $trans['currency'] == $this->request->input('currency')
            && $trans['amount'] == $this->request->input('amount')*100;
    }

    public function checkCurrency($currency)
    {
        return $currency == $this->request->input('currency');
    }
}