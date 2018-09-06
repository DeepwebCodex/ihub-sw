<?php

namespace App\Console\Commands;

use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Stringy\StaticStringy;

class CancelPendingOperations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:cancel-pending {batch=80 : One time operations batch size} {expire=2 : expiration date limit in days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels account manager pending operations by timeout';

    protected $batchSize = 80;
    protected $expirationDays = 2; //set in days

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->batchSize = (int)$this->argument('batch');
        $this->expirationDays = (int)$this->argument('expire');

        $expirationDate = Carbon::now()->subDay($this->expirationDays)->format('Y-m-d');

        //Service ids for deep integration are excluded from query to prevent uncorrectable results
        $services = $this->getServices([config('integrations.inspired.service_id'), config('integrations.virtualBoxing.service_id')]);

        if (!$services) {
            $this->error("There is no services found in config \n");
            return -1;
        }

        try {

            $operations = app('AccountManager')->getOperationByQuery([
                'select' => ['id', 'object_id', 'service_id'],
                'where' => [
                    ['status', 'pending'],
                    ['service_id', array_keys($services)],
                    ['dt', '>', "{$expirationDate}"],
                    ['move', TransactionRequest::D_WITHDRAWAL]
                ],
                'limit' => $this->batchSize
            ]);
        } catch (GenericApiHttpException $e) {
            if(!empty($e->getMessage())) {
                throw $e;
            } else {
                $this->info("There is no pending operations \n");
                return;
            }
        }

        $operations = collect($operations);

        if ($operations->isEmpty()) {
            $this->info("There is no pending operations \n");
            return;
        }

        $operations = $operations->groupBy('service_id');

        $operations->each(function (Collection $groupOperations, $serviceId) use ($services, $expirationDate) {
            $this->info("\n Canceling BET operations for service {$services[$serviceId]} ({$serviceId}) \n");
            $bar = $this->output->createProgressBar($groupOperations->count());

            foreach ($groupOperations as $operationItem) {

                $id = data_get($operationItem, 'id');
                $objectId = data_get($operationItem, 'object_id');
                
                //hard fix for bel
                if(data_get($operationItem, 'currency') === 'BYN' || data_get($operationItem, 'currency') === 'BYR'){
                    $this->info("\n Passed  currency of belarus");
                    continue;
                }

                try {

                    app('AccountManager')->cancelTransactionHard($id, $objectId, "Canceled by expiration date <{$expirationDate} \n");

                } catch (GenericApiHttpException $e) {
                    $message = "Failed to cancel operation id: {$id}, object_id: {$objectId} \n";
                    app('AppLog')->warning($message);
                    $this->error($message);
//                    return -1;
                }

                $bar->advance();
            }
            $bar->finish();
            $this->info("\n");
        });

        $this->info("\n Done \n");
    }

    protected function getServices(array $exclude = []): array
    {
        $services = config('integrations');

        foreach ($services as $service => $data) {
            if (isset($data['service_id']) && !in_array($data['service_id'], $exclude)) {
                $services[$data['service_id']] = (string)StaticStringy::humanize($service);
            }

            unset($services[$service]);
        }

        return $services;
    }
}
