<?php

namespace App\Log\RabbitMq;

use AMQPConnection;
use App\Log\RabbitMq\Formatter\RabbitFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

class RabbitHandler extends BaseHandler
{
    private $connection;
    private $channel;
    private $exchange;
    private $prefix;
    private $default_exchange;

    private $queueManager;

    public function __construct(AMQPConnection $connection, RabbitQueueManager $queueManager, $prefix, $default_exchange, int $level = Logger::DEBUG)
    {
        $this->connection = $connection;

        if(!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        $this->channel = new \AMQPChannel($connection);

        $this->queueManager = $queueManager;
        $this->level = $level;

        $this->prefix = $prefix;
        $this->default_exchange = $default_exchange;

        $this->exchange = $queueManager->setUpQueue($this->channel, $prefix, $default_exchange);
    }

    protected function sendRecord($record)
    {
        $level = strtolower(array_get($record, 'level_name'));

        if($level) {
            $this->exchange->publish(json_encode(array_get($record, 'formatted')), $level, AMQP_NOPARAM, [
                'content_type' => 'application/json',
                'delivery_mode' => 2
            ]);
        }
    }

    protected function sendBatch($records)
    {
        if($records) {

            foreach ($records as $record) {
                $this->sendRecord($record);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $to_send = [];

        foreach ($records as $key => $record){
            if (!$this->isHandling($record)) {
                continue;
            }

            $record = $this->processRecord($record);

            $record['formatted'] = $this->getFormatter()->format($record);

            $to_send[$key] = $record;
        }

        $this->sendBatch($to_send);
    }

    protected function close(){
        if($this->connection->isConnected()){
            $this->connection->disconnect();
        }
    }

    /**
     * Gets the default formatter.
     *
     * @return FormatterInterface
     */
    protected function getDefaultFormatter()
    {
        return new RabbitFormatter();
    }

    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Exception $e) {
            // do nothing
        } catch (\Throwable $e) {
            // do nothing
        }
    }
}