<?php

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\GetCommitQueueData;
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
        $commitSource = new GetCommitQueueData();
        try {
            $data = $commitSource->getData();
        } catch (RequestException $re) {
            $message = 'Request has error.  Request: ' . str($re->getRequest());
            if ($re->hasResponse()) {
                $message .= " Response" . str($re->getResponse());
            }
            $this->handleError($message, GetCommitQueueData::MODULE, $re->getLine());
        } catch (Exception $ex) {
            $this->handleError($ex->getMessage(), GetCommitQueueData::MODULE, $ex->getLine());
        }
    }

}
