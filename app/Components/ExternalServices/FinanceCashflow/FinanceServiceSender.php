<?php

namespace App\Components\ExternalServices\FinanceCashflow;

use App\Components\ExternalServices\FinanceCashflow\Traits\FinanceServiceRequest;
use Symfony\Component\Translation\Exception\InvalidResourceException;

class FinanceServiceSender
{
    use FinanceServiceRequest;

    protected $host;
    protected $port;

    const BET = 3;
    const BET_LOSE = 4;
    const BET_WIN  = 5;
    const BET_PAYMENT = 6;
    const BET_CALCULATION = 29;

    public function __construct()
    {
        if(!config('finance.service.host')) {
            throw new InvalidResourceException('Invalid Finance API configuration');
        }

        $this->host = config('finance.service.host');
        $this->port = config('finance.service.port');
    }

    public function saveBet(
        int $partner_id,
        int $cashdesk_id,
        string $currency,
        int $operation_id,
        string $date,
        float $amount,
        int $user_id,
        int $service_id
    )
    {
        return $this->saveTransaction(static::BET, [
            'partner_id' => $partner_id,
            'cashdesk'   => $cashdesk_id,
            'currency'   => $currency,
            'id'         => $operation_id,
            'tax_type'   => 0,
            'tax_sum'    => 0,
            'dt'         => $date,
            'amount'     => $amount,
            'user_type'  => 1,
            'user_id'    => $user_id,
            'service_id' => $service_id
        ]);
    }

    public function saveWin(
        int $partner_id,
        int $cashdesk_id,
        string $currency,
        int $operation_id,
        string $date,
        float $amount,
        int $user_id,
        int $service_id
    )
    {
        return $this->saveTransaction(static::BET_WIN, [
            'partner_id' => $partner_id,
            'cashdesk'   => $cashdesk_id,
            'currency'   => $currency,
            'id'         => $operation_id,
            'tax_type'   => 0,
            'tax_sum'    => 0,
            'dt'         => $date,
            'amount'     => $amount,
            'user_type'  => 1,
            'user_id'    => $user_id,
            'service_id' => $service_id
        ]);
    }

    public function saveLose(
        int $partner_id,
        int $cashdesk_id,
        string $currency,
        int $operation_id,
        string $date,
        float $amount,
        int $user_id,
        int $service_id
    )
    {
        return $this->saveTransaction(static::BET_LOSE, [
            'partner_id' => $partner_id,
            'cashdesk'   => $cashdesk_id,
            'currency'   => $currency,
            'id'         => $operation_id,
            'tax_type'   => 0,
            'tax_sum'    => 0,
            'dt'         => $date,
            'amount'     => $amount,
            'user_type'  => 1,
            'user_id'    => $user_id,
            'service_id' => $service_id
        ]);
    }

    public function savePayment(
        int $partner_id,
        int $cashdesk_id,
        string $currency,
        int $operation_id,
        string $date,
        float $amount,
        int $user_id,
        int $service_id
    )
    {
        return $this->saveTransaction(static::BET_PAYMENT, [
            'partner_id' => $partner_id,
            'cashdesk'   => $cashdesk_id,
            'currency'   => $currency,
            'id'         => $operation_id,
            'tax_type'   => 0,
            'tax_sum'    => 0,
            'dt'         => $date,
            'amount'     => $amount,
            'user_type'  => 1,
            'user_id'    => $user_id,
            'service_id' => $service_id
        ]);
    }
    
    public function saveCalculation(
        int $partner_id,
        int $cashdesk_id,
        string $currency,
        int $operation_id,
        string $date,
        float $amount,
        int $user_id,
        int $service_id
    )
    {
        return $this->saveTransaction(static::BET_CALCULATION, [
            'partner_id' => $partner_id,
            'cashdesk'   => $cashdesk_id,
            'currency'   => $currency,
            'id'         => $operation_id,
            'tax_type'   => 0,
            'tax_sum'    => 0,
            'dt'         => $date,
            'amount'     => $amount,
            'user_type'  => 1,
            'user_id'    => $user_id,
            'service_id' => $service_id
        ]);
    }

    public function saveTransaction(int $type, array $data)
    {
        return $this->send('save_transaction', [
            'transaction_type' => $type,
            'data' => $data
        ]);
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     */
    private function send($method, array $params)
    {
        return $this->sendPost($this->buildHost($method), $params, 3);
    }

    /**
     * @param string $method
     * @return string
     */
    private function buildHost(string $method): string
    {
        return $this->host . ':' . $this->port . '/cashflow/' . $method;
    }
}
