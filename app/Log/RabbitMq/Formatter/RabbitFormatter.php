<?php

namespace App\Log\RabbitMq\Formatter;

use Monolog\Formatter\FormatterInterface;

/**
 * Class AppFormatter
 * @package App\Log\Monolog\Formatter
 */
class RabbitFormatter implements FormatterInterface
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
                'ip'  => request()->getClientIp(),
                'project' => config('app.name'),
                'msg' => $record['message']
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
