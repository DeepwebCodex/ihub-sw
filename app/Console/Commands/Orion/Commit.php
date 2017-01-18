<?php

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\OperationsProcessor;
use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Integrations\MicroGaming\Orion\SoapEmul;
use App\Components\Integrations\MicroGaming\Orion\SourceProcessor;
use App\Http\Requests\Validation\Orion\CommitValidation;
use App\Http\Requests\Validation\Orion\ManualValidation;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use function GuzzleHttp\Psr7\str;

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
        $sourceProcessor = new SourceProcessor();
        $soapEmul = new SoapEmul();
        $dataResponse = array();
        try {
            $commitSource = new GetCommitQueueData($soapEmul, $sourceProcessor);
            $data = $commitSource->getData();
            $validatorCommitData = new CommitValidation();
            $validatorCommitData->validateBaseStructure($data);
            $handleCommitRes = OperationsProcessor::commit($data);
            $manualValidateBet = new ManuallyValidateBet($soapEmul, $sourceProcessor);
            $dataResponse = $manualValidateBet->getData($handleCommitRes);
            $mBetValidation = new ManualValidation();
            $mBetValidation->validateBaseStructure($dataResponse);
        } catch (RequestException $re) {
            $message = 'Request has error.  Request: ' . str($re->getRequest());
            if ($re->hasResponse()) {
                $message .= " Response" . str($re->getResponse());
            }
            $this->handleError($message, GetCommitQueueData::MODULE, $re->getLine());
        } catch (Exception $ex) {
            $this->handleError($ex->getMessage(), GetCommitQueueData::MODULE, $ex->getLine());
        }

        $this->handleSuccess($dataResponse);
    }

}
