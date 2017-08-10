<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\SeamlessWalletCore\Models\Transactions;
use Illuminate\Console\Command;
use Stringy\StaticStringy;

/**
 * Class TransactionHistoryStatusUpdate
 * @package App\Console\Commands
 */
class TransactionHistoryStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-history:status-update {batch=80 : One time operations batch size} {expire=2 : expiration date limit in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update transaction history records status with info from accounting';

    protected $batchSize = 80;
    protected $expirationDays = 1; //set in days

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException
     */
    public function handle()
    {
        $this->info('Transaction history status update started');

        $this->batchSize = (int)$this->argument('batch');
        $this->expirationDays = (int)$this->argument('expire');

        $expirationDate = Carbon::now()->subDay($this->expirationDays)->format('Y-m-d H:i:s');

        // Service ids for deep integration are excluded from query to prevent uncorrectable results
        $services = $this->getServices([
            \config('integrations.inspired.service_id'),
            \config('integrations.virtualBoxing.service_id')
        ]);

        if (!$services) {
            $this->error("There is no services found in config \n");
            return -1;
        }

        $pendingTransactions = $this->getPendingTransactions($expirationDate, $services);

        $bar = $this->output->createProgressBar(count($pendingTransactions));
        foreach ($pendingTransactions as $pendingTransaction) {
            $bar->advance();
            $this->info("\n");
            try {
                $operation = $this->getOperationFromAccounting($pendingTransaction);
            } catch (\Exception $exception) {
                $this->error(
                    "There was an error in getting operation info from Account Manager - id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
                );
                continue;
            }
            if (empty($operation)) {
                $this->info(
                    "There is no operation in Account Manager - id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
                );
                continue;
            }
            $this->updateTransactionStatus($pendingTransaction, $operation['status']);
        }
        $bar->finish();
        $this->info("\n");

        $this->info('Transaction history status update completed');
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
     */
    protected function getPendingTransactions($expirationDate, $services)
    {
        return Transactions::where([
            ['status', 'pending'],
            ['created_at', '<', $expirationDate]
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
     * @param $pendingTransaction
     * @param $newStatus
     */
    protected function updateTransactionStatus($pendingTransaction, $newStatus)
    {
        if ($newStatus && $newStatus !== $pendingTransaction->status) {
            $this->info(
                "New status in Account Manager - status: {$newStatus}, id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
            );
            $pendingTransaction->status = $newStatus;
            $pendingTransaction->save();
            return;
        }
        $this->info(
            "Same status in Account Manager - status: {$pendingTransaction->status}, id: {$pendingTransaction->id}, operation_id: {$pendingTransaction->operation_id} \n"
        );
    }
}
