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
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitHandler implements HandlerInterface
{
    private $connection;
    private $channel;
    private $prefix;
    private $default_exchange;

    private $queueManager;

    protected $level = Logger::DEBUG;

    /**
     * @var FormatterInterface
     */
    protected $formatter;
    protected $processors = [];

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
    public function isHandling(array $record)
    {
        return $record['level'] >= $this->level;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->sendRecord($record);

        return false;
    }

    /**
     * Processes a record.
     *
     * @param  array $record
     * @return array
     */
    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
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

    /**
     * {@inheritdoc}
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
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