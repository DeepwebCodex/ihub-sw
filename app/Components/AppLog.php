<?php

namespace App\Components;

use Illuminate\Support\Facades\Log;

/**
 * Class AppLog
 * @package App
 */
class AppLog
{
    /**
     * @param string $node
     * @param string $module
     * @param string $line
     * @return array
     */
    private function composeContext($node, $module, $line)
    {
        if (!$node || !$module || !$line) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

            $trace = array_filter($trace, function ($elem) {
                return ($elem['class'] !== __CLASS__);
            });
            $trace = array_values($trace);

            list($traceLineInfo, $traceClassInfo) = $trace;

            $node = $node ?: (new \ReflectionClass($traceClassInfo['class']))->getShortName();
            $module = $module ?: $traceClassInfo['function'];
            $line = $line ?: $traceLineInfo['line'];
        }

        return compact('node', 'module', 'line');
    }

    /**
     * Log an alert message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function alert($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log a critical message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function critical($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log an error message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function error($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log a warning message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function warning($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log a notice to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function notice($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log an informational message to the logs.
     *
     * @param  string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function info($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
    }

    /**
     * Log a debug message to the logs.
     *
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    public function debug($message, $node = '', $module = '', $line = '')
    {
        return $this->write(__FUNCTION__, $message, $node, $module, $line);
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
    public function log($level, $message, $node = '', $module = '', $line = '')
    {
        return $this->write($level, $message, $node, $module, $line);
    }

    /**
     * Dynamically pass log calls into the writer.
     *
     * @param string $level
     * @param string $message
     * @param string $node
     * @param string $module
     * @param string $line
     */
    private function write($level, $message, $node = '', $module = '', $line = '')
    {
        $context = $this->composeContext($node, $module, $line);
        return Log::write($level, $message, $context);
    }
}
