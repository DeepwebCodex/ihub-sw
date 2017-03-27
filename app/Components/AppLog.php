<?php

namespace App\Components;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Support\Facades\Log;

/**
 * Class AppLog
 * @package App
 */
class AppLog
{

    private $controller = null;
    private $method = null;
    private $requestId = null;

    public function __construct()
    {
        $this->requestId = gen_uid();
    }

    /**
     * @param string $node
     * @param string $module
     * @param string $line
     * @param string $group
     * @return array
     */
    private function composeContext($node, $module, $line, $group = '')
    {
        if (!$node || !$module || !$line) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

            $trace = array_filter($trace, function ($elem) use($node, $module){
                if(isset($elem['class'])) {
                    if (!$node && preg_match("/Controller$/", $elem['class'])) {
                        if (is_subclass_of($elem['class'], BaseApiController::class)) {
                            $this->controller = $elem['class'];
                            $this->method = $elem['function'];
                        }
                    }
                    return ($elem['class'] !== __CLASS__);
                }

                return false;
            });

            $trace = array_values($trace);

            if($this->controller && $this->method){
                $node = $node ?: (new \ReflectionClass($this->controller))->getShortName();
                $module = $module ?: $this->method;
            }

            list($traceLineInfo, $traceClassInfo) = $trace;

            $node = $node ?: (new \ReflectionClass($traceClassInfo['class']))->getShortName();
            $module = $module ?: $traceClassInfo['function'];
            $line = $line ?: array_get($traceLineInfo, 'line');

            $requestId = $this->requestId;
        }

        return compact('node', 'module', 'line', 'requestId', 'group');
    }

    /**
     * Log an alert message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function alert($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function critical($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log an error message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function error($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function warning($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log a notice to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function notice($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function info($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function debug($message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line, $group);
    }

    /**
     * Log a message to the logs.
     *
     * @param string $level
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function log($level, $message, $node = '', $module = '', $line = '', $group = '')
    {
        return $this->write($level, $message, $node, $module, $line, $group);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param string $level
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     * @param string $group
     * @return
     */
    private function write($level, $message, $node = '', $module = '', $line = '', $group = '')
    {
        $context = $this->composeContext($node, $module, $line, $group);

        if(is_array($message)){
            $message = collect($message);
        }

        return Log::write($level, $message, $context);
    }
}
