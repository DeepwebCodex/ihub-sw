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
        return array_merge(
            [
                'level' => strtolower($record['level_name']),
                'time' => $record['datetime']->getTimestamp(),
                'msg' => wordwrap(utf8_encode($record['message']), 150),
            ],
            $record['context']
        );
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
