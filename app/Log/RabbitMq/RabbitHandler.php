<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/11/16
 * Time: 12:13 PM
 */

namespace App\Log\RabbitMq;

use App\Log\RabbitMq\Formatter\RabbitFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitHandler extends BaseHandler
{
    private $connection;
    private $channel;
    private $prefix;
    private $default_exchange;

    private $queueManager;

    public function __construct(AMQPStreamConnection $connection, RabbitQueueManager $queueManager, $prefix, $default_exchange, int $level = Logger::DEBUG)
    {
        $this->connection = $connection;
        $this->channel = $connection->channel();

        $this->queueManager = $queueManager;
        $this->level = $level;

        $this->prefix = $prefix;
        $this->default_exchange = $default_exchange;

        $queueManager->setUpQueue($this->channel, $prefix, $default_exchange);
    }

    protected function sendRecord($record)
    {
        $level = strtolower(array_get($record, 'level_name'));

        if($level) {
            $msg = new AMQPMessage(json_encode(array_get($record, 'formatted')), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $this->channel->basic_publish($msg, $this->prefix . $this->default_exchange, $level);
        }
    }

    protected function sendBatch($records)
    {
        if($records) {

            foreach ($records as $record) {
                $level = strtolower(array_get($record, 'level_name'));

                if($level) {
                    $msg = new AMQPMessage(json_encode(array_get($record, 'formatted')), [
                        'content_type' => 'application/json',
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                    ]);

                    $this->channel->batch_basic_publish($msg, $this->prefix . $this->default_exchange, $record['level']);
                }
            }

            $this->channel->publish_batch();
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
            $this->connection->close();
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