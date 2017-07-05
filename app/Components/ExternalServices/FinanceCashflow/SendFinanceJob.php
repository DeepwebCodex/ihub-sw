<?php

namespace App\Components\ExternalServices\FinanceCashflow;

use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFinanceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @param $transaction
     */
    public function __construct(Transactions $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @param FinanceServiceSender $sendService
     * @return void
     * @throws \Exception
     */
    public function handle(FinanceServiceSender $sendService)
    {
        $method = $this->selectMethod();

        $sendService->$method(
            $this->transaction->partner_id,
            $this->transaction->cashdesk,
            $this->transaction->currency,
            $this->transaction->operation_id,
            $this->transaction->updated_at,
            $this->transaction->amount,
            $this->transaction->user_id,
            $this->transaction->service_id
        );
    }

    protected function selectMethod() : string
    {
        if($this->transaction->getAttributeValue('transaction_type') == TransactionRequest::TRANS_BET) {
            return 'saveBet';
        }

        if($this->transaction->getAttributeValue('amount') > 0) {
            return 'saveWin';
        }

        return 'saveLose';
    }
}
