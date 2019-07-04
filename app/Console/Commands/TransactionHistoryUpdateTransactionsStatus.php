<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use iHubGrid\SeamlessWalletCore\Transactions\Events\AfterCompleteTransactionEvent;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Console\Command;
use Stringy\StaticStringy;

class TransactionHistoryUpdateTransactionsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-history:status-update {dateFrom} {dateTo} {batchSize=1000} {serviceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update transaction history records status with info from accounting';

    protected $batchSize;
    protected $dateFrom;
    protected $dateTo;
    private $sameStatusCount = 0;
    private $updatedStatusCount = 0;
    private $noOperationsInAccountManagerCount = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException
     */
    public function handle()
    {
        $this->info('Transaction history status update started');

        $this->batchSize = (int)$this->argument('batchSize');
        $this->dateFrom = new Carbon($this->argument('dateFrom'));
        $this->dateTo = new Carbon($this->argument('dateTo'));
        $services = ($this->argument('serviceId') !== null) ? [$this->argument('serviceId')] : [];

        if (empty($services)) {
            // Service ids for deep integration are excluded from query to prevent uncorrectable results
            $services = $this->getServices([
                \config('integrations.inspired.service_id'),
                \config('integrations.virtualBoxing.service_id')
            ]);
        }

        if (!$services) {
            $this->error("There is no services found in config \n");
            return -1;
        }

        $pendingTransactions = $this->getTransactions($services);

        $bar = $this->output->createProgressBar(count($pendingTransactions));
        foreach ($pendingTransactions as $pendingTransaction) {
            $bar->advance();
            try {
                $operation = $this->getOperationFromAccounting($pendingTransaction);
            } catch (\Exception $exception) {
                $this->error(
                    "There was an error in getting operation info from Account Manager - id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
                );
                continue;
            }
            if (empty($operation)) {
                $this->noOperationsInAccountManagerCount++;
//                $this->info(
//                    "There is no operation in Account Manager - id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
//                );
                continue;
            }
            $this->updateTransactionStatus($pendingTransaction, $operation['status']);
        }
        $bar->finish();
        $this->info("\n");

        $this->info('Transaction history status update completed');
        $this->info('Transactions updated count: ' . $this->updatedStatusCount);
        $this->info('Transactions skipped count: ' . $this->sameStatusCount);
        $this->info('No operations in account manager count: ' . $this->sameStatusCount);
    }

    /**
     * @param array $exclude
     * @return array
     */
    protected function getServices(array $exclude = []): array
    {
        $services = \config('integrations');

        foreach ($services as $service => $data) {
            if (isset($data['service_id']) && !\in_array($data['service_id'], $exclude)) {
                $services[$data['service_id']] = (string)StaticStringy::humanize($service);
            }
            unset($services[$service]);
        }

        return $services;
    }

    /**
     * @param $expirationDate
     * @param $services
     * @return array
     */
    protected function getTransactions($services): array
    {
        return Transactions::where([
            ['created_at', '>=', $this->dateFrom],
            ['created_at', '<=', $this->dateTo],
        ])
            ->whereIn('service_id', \array_keys($services))
            ->limit($this->batchSize)
            ->get()
            ->all();
    }

    /**
     * @param $pendingTransaction
     * @return mixed
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException
     */
    protected function getOperationFromAccounting($pendingTransaction)
    {
        \app('AccountManager')->selectAccounting(
            $pendingTransaction->partner_id,
            $pendingTransaction->cashdesk
        );
        try {
            $operation = \app('AccountManager')->getOperationByQuery([
                'select' => ['status'],
                'where' => [
                    ['id', $pendingTransaction->operation_id]
                ],
                'limit' => 1
            ]);
        } catch (GenericApiHttpException $exception) {
            if (!empty($exception->getMessage())) {
                throw $exception;
            }
            return null;
        }
        if (isset($operation) && \count($operation)) {
            $operation = \reset($operation);
        }
        return $operation;
    }

    /**
     * @param Transactions $transaction
     * @param $newStatus
     */
    protected function updateTransactionStatus(Transactions $transaction, $newStatus)
    {
        if (!empty($newStatus) && $newStatus !== $transaction->status) {
            $this->updatedStatusCount++;
//            $this->info(
//                "New status in Account Manager - status: {$newStatus}, id: {$transaction->id}, operation_id: {$transaction->operation_id} \n"
//            );
            $transaction->status = $newStatus;
            $transaction->save();

            if ($newStatus === 'complete') {
                $this->dispatchAfterCompleteTransactionEvent($transaction);
            }

            return;
        }
        $this->sameStatusCount++;
//        $this->info(
//            "Same status in Account Manager - status: {$transaction->status}, id: {$transaction->id}, operation_id: {$transaction->operation_id} \n"
//        );
    }

    /**
     * @param Transactions $pendingTransaction
     */
    protected function dispatchAfterCompleteTransactionEvent(Transactions $pendingTransaction)
    {
        $transactionRequest = new TransactionRequest(
            $pendingTransaction->service_id,
            $pendingTransaction->object_id,
            $pendingTransaction->user_id,
            $pendingTransaction->currency,
            $pendingTransaction->move,
            $pendingTransaction->amount,
            $pendingTransaction->transaction_type,
            $pendingTransaction->foreign_id,
            $pendingTransaction->game_id,
            $pendingTransaction->partner_id,
            $pendingTransaction->cashdesk,
            $pendingTransaction->client_ip
        );
        \event(new AfterCompleteTransactionEvent($transactionRequest));
    }
}
