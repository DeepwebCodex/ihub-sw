<?php

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\CommitProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use Illuminate\Console\Command;

class Commit extends Command {

    use Operation;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orion:commit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commit orion transactions';

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
        $commitSource = app(GetCommitQueueData::class, [$soapEmul, $sourceProcessor]);
        $validatorCommitData = app(CommitValidation::class);
        $manualValidateBet = app(ManuallyValidateBet::class, [$soapEmul, $sourceProcessor]);
        $mBetValidation = app(ManualValidation::class);
        $operationsProcessor = app(CommitProcessor::class);
        $this->make($commitSource, $validatorCommitData, $operationsProcessor, $manualValidateBet, $mBetValidation);
    }

}
