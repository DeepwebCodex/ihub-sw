<?php

namespace App\Log\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Class AppFormatter
 * @package App\Log\Monolog\Formatter
 */
class AppFormatter implements FormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record)
    {
        return [
            'level' => strtolower($record['level_name']),
            'time' => $record['datetime']->getTimestamp(),
            'msg' => wordwrap(utf8_encode($record['message']), 150),
            'node' => $record['node'] ?? '',
            '_module' => $record['module'] ?? '',
            '_line' => $record['line'] ?? '',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }
}
