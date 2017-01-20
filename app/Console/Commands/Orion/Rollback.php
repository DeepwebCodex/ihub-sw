<?php

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\CommitRollbackProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetRollbackQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Components\Transactions\TransactionRequest;
use App\Http\Requests\Validation\Orion\ManualValidation;
use App\Http\Requests\Validation\Orion\RollbackValidation;
use Illuminate\Console\Command;
use function app;

class Rollback extends Command {

    use Operation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orion:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback orion transactions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $sourceProcessor = app(SourceProcessor::class);
        $soapEmul = app(SoapEmul::class);
        $requestQueueData = app(GetRollbackQueueData::class, [$soapEmul, $sourceProcessor]);
        $validatorQueueData = app(RollbackValidation::class);
        $requestResolveData = app(ManuallyValidateBet::class, [$soapEmul, $sourceProcessor]);
        $validatorResolveData = app(ManualValidation::class);
        $operationsProcessor = app(CommitRollbackProcessor::class, ['RollbackQueue',
            TransactionRequest::TRANS_REFUND]);
        $this->make($requestQueueData, $validatorQueueData, $operationsProcessor, $requestResolveData, $validatorResolveData);
    }

}
