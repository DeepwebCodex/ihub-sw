<?php

namespace App\Console\Commands\Vermantia;

use App\Components\AppLog;
use Exception;
use Illuminate\Console\Command;

abstract class BaseEventCommand extends Command
{
    protected $node;
    protected $group;

    protected $attempt = 0;
    protected $retryAttempts = 3;

    public function __construct(string $node = null, string $group = null)
    {
        parent::__construct();

        $this->node = $node;
        $this->group = $group;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->runHandle();
        } catch (\Exception $e) {
            $this->failing($e, $this->attempt);
            $this->report($e);
            exit(-1);
        }
    }

    protected function respond($message)
    {
        if(is_string($message)) {
            $message = [
                'message' => $message
            ];
        }

        app('AppLog')->info($message, $this->node, class_basename(get_class($this)), '', $this->group);
    }

    protected function respondOk($message)
    {
        $this->respond($message);
        exit(0);
    }

    /**
     * @param Exception $exception
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        try {
            /**@var AppLog $logger*/
            $logger = app('AppLog');
        } catch (Exception $ex) {
            throw $exception; // throw the original exception
        }

        $errorThrownBy = $this->composeContextFromTrace($exception->getTrace());

        $logger->error(
            collect([
                'message' => $exception->getMessage(),
                'trace' => json_encode($exception->getTrace())
            ]),
            $errorThrownBy['node'],
            $errorThrownBy['module'],
            $errorThrownBy['line'],
            $this->group
        );
    }

    /**
     * @param $trace
     * @return array
     */
    private function composeContextFromTrace($trace)
    {
        $trace = array_filter($trace, function ($elem){
            if(isset($elem['class'])) {
                return ($elem['class'] !== __CLASS__);
            }

            return false;
        });

        $trace = array_values($trace);

        list($traceLineInfo) = $trace;

        $node = $traceLineInfo['class'] ?? '';
        $module = $traceLineInfo['function'] ?? '';
        $line = $traceLineInfo['line'] ?? '';

        return compact('node', 'module', 'line');
    }

    abstract public function runHandle();

    abstract protected function failing(Exception $e, int $attempt =0);
}
