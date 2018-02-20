<?php

namespace App\Components\ExternalServices\FinanceCashflow;

use Carbon\Carbon;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterCompleteTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterPendingTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\Events\TransactionEventInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendFinanceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**@var TransactionRequest*/
    protected $transaction;

    protected $event;

    /**
     * Create a new job instance.
     *
     * @param $transactionEvent
     */
    public function __construct(TransactionEventInterface $transactionEvent)
    {
        $this->transaction = $transactionEvent->getTransactionRequest();
        $this->event = $transactionEvent;
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

        if($method == 'none') {
            return;
        }

        if($method == 'saveLose') {
            $betTransaction = Transactions::getLastBetTransaction(
                $this->transaction->service_id,
                $this->transaction->user_id,
                $this->transaction->currency,
                $this->transaction->partner_id
                );
            if($betTransaction) {
                $this->transaction->amount = $betTransaction->getAttributeValue('amount');
            }
        }

        $sendService->$method(
            $this->transaction->partner_id,
            $this->transaction->cashdesk_id,
            $this->transaction->currency,
            $this->transaction->object_id,
            Carbon::now('UTC')->format('Y-m-d H:i:s'),
            abs($this->transaction->amount),
            $this->transaction->user_id,
            $this->transaction->service_id
        );
    }

    protected function selectMethod() : string
    {
        if($this->transaction->transaction_type == TransactionRequest::TRANS_BET && 
            $this->event instanceof AfterPendingTransactionEvent) {
            return 'saveBet';
        }

        if(
            abs($this->transaction->amount) > 0 &&
            $this->event instanceof AfterPendingTransactionEvent &&
            $this->transaction !== TransactionRequest::TRANS_BET
        ) {
            return 'saveWin';
        } elseif (
            $this->event instanceof AfterPendingTransactionEvent &&
            $this->transaction !== TransactionRequest::TRANS_BET
        ) {
            return 'saveLose';
        }

        if(
            abs($this->transaction->amount) > 0 &&
            $this->event instanceof AfterCompleteTransactionEvent &&
            $this->transaction !== TransactionRequest::TRANS_BET
        ) {
            return 'savePayment';
        }

        return 'none';
    }
}
