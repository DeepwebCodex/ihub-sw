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
        $message = $record['message'];

        $decodedMessage = json_decode($record['message'], true);
        if ($decodedMessage && json_last_error() === JSON_ERROR_NONE) {
            $message = $decodedMessage;
        }

        return array_merge(
            [
                'level' => strtolower($record['level_name']),
                'time' => $record['datetime']->getTimestamp(),
                'ip'  => get_client_ip(),
                'project' => config('app.name'),
                'msg' => $message
            ],
            [
                'context' => $record['context']
            ]
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
