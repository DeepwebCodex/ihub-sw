<?php

namespace App\Queue;

use AMQPChannel;
use AMQPConnection;
use AMQPEnvelope;
use AMQPExchange;
use App\Facades\AppLog;
use App\Queue\Jobs\RabbitMQJob;
use DateTime;
use ErrorException;
use Exception;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;

/**
 * Class RabbitMQQueue
 * @package App\Queue
 */
class RabbitMQQueue extends Queue implements QueueContract
{
    /**
     * Used for retry logic, to set the retries on the message metadata instead of the message body.
     */
    const ATTEMPT_COUNT_HEADERS_KEY = 'attempts_count';

    /**
     * @var AMQPConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPExchange
     */
    protected $exchange;

    protected $declaredQueue;

    protected $declaredExchange;

    protected $declareExchange;
    protected $declaredExchanges = [];
    protected $declareBindQueue;
    protected $sleepOnError;

    protected $declaredQueues = [];

    protected $defaultQueue;
    protected $configQueue;
    protected $configExchange;

    /**
     * @var int
     */
    private $attempts;

    /**
     * @var string
     */
    private $correlationId;

    /**
     * @param AMQPConnection $amqpConnection
     * @param array $config
     */
    public function __construct(AMQPConnection $amqpConnection, $config)
    {
        $this->connection = $amqpConnection;
        $this->connection->connect();

        $this->defaultQueue = $config['queue'];
        $this->configQueue = $config['queue_params'];
        $this->configExchange = $config['exchange_params'];
        $this->declareExchange = $config['exchange_declare'];
        $this->declareBindQueue = $config['queue_declare_bind'];
        $this->sleepOnError = isset($config['sleep_on_error']) ? $config['sleep_on_error'] : 5;

        $this->channel = new AMQPChannel($this->connection);
    }

    /**
     * Get the size of the queue.
     *
     * @param  string $queue
     * @return int
     */
    public function size($queue = null)
    {
        // TODO: Implement size() method.
    }

    /**
     * Push a new job onto the queue.
     *
     * @param string $job
     * @param mixed $data
     * @param string $queue
     *
     * @return bool
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, []);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param string $payload
     * @param string $queue
     * @param array $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        try {
            $queue = $this->getQueueName($queue);
            $this->declareQueue($queue);
            if (isset($options['delay']) && $options['delay'] > 0) {
                list($queue, $exchange) = $this->declareDelayedQueue($queue, $options['delay']);
            } else {
                list($queue, $exchange) = $this->declareQueue($queue);
            }

            $headers = [
                'Content-Type' => 'application/json',
                'delivery_mode' => 2,
            ];

            $message = ['payload' => $payload];

            if (isset($this->attempts) === true) {
                $message['application_headers'] = [self::ATTEMPT_COUNT_HEADERS_KEY => ['I', $this->attempts]];
            }

            $correlationId = $this->getCorrelationId();
            $message['correlation_id'] = $correlationId;

            // push task to a queue
            $this->declaredExchange->publish($this->serializeMessage($message), $exchange, AMQP_NOPARAM, $headers);

            return $correlationId;
        } catch (ErrorException $exception) {
            $this->reportConnectionError('pushRaw', $exception);
        }

        return null;
    }

    /**
     * @param string $queue
     *
     * @return string
     */
    private function getQueueName($queue)
    {
        return $queue ?: $this->defaultQueue;
    }

    /**
     * @param $name
     *
     * @return array
     */
    private function declareQueue($name)
    {
        $name = $this->getQueueName($name);
        $exchange = $this->configExchange['name'] ?: $name;

        if ($this->declareExchange && !in_array($exchange, $this->declaredExchanges)) {
            // declare exchange
            $this->declareAmqpExchange($name);

            $this->declaredExchanges[] = $exchange;
        }

        if ($this->declareBindQueue && !in_array($name, $this->declaredQueues)) {
            // declare queue
            $this->declareAmqpQueue($name);

            $this->declareAmqpExchange($name);

            // bind queue to the exchange
            $this->declaredQueue->bind($exchange, $name);

            $this->declaredQueues[] = $name;
        }

        return [$name, $exchange];
    }

    /**
     * @param $name
     */
    private function declareAmqpExchange($name)
    {
        $exchangeObj = new AMQPExchange($this->channel);
        $exchangeObj->setName($name);
        $exchangeObj->setType($this->configExchange['type']);
        $flags = 0;
        if ($this->configExchange['passive']) {
            $flags |= AMQP_PASSIVE;
        }
        if ($this->configExchange['durable']) {
            $flags |= AMQP_DURABLE;
        }
        if ($this->configExchange['auto_delete']) {
            $flags |= AMQP_AUTODELETE;
        }
        $exchangeObj->setFlags($flags);
        $exchangeObj->declareExchange();

        $this->declaredExchange = $exchangeObj;
    }

    private function declareAmqpQueue($name, $arguments = null)
    {
        $queueObj = new \AMQPQueue($this->channel);
        $queueObj->setName($name);
        $flags = 0;
        if ($this->configQueue['passive']) {
            $flags |= AMQP_PASSIVE;
        }
        if ($this->configQueue['durable']) {
            $flags |= AMQP_DURABLE;
        }
        if ($this->configQueue['exclusive']) {
            $flags |= AMQP_EXCLUSIVE;
        }
        if ($this->configQueue['auto_delete']) {
            $flags |= AMQP_AUTODELETE;
        }
        $queueObj->setFlags($flags);
        if ($arguments) {
            $queueObj->setArguments($arguments);
        }
        $queueObj->declareQueue();

        $this->declaredQueue = $queueObj;
    }

    /**
     * @param string $destination
     * @param DateTime|int $delay
     *
     * @return string
     */
    private function declareDelayedQueue($destination, $delay)
    {
        $delay = $this->getSeconds($delay);
        $destination = $this->getQueueName($destination);
        $destinationExchange = $this->configExchange['name'] ?: $destination;
        $name = $this->getQueueName($destination) . '_deferred_' . $delay;
        $exchange = $this->configExchange['name'] ?: $destination;

        // declare exchange
        if (!in_array($exchange, $this->declaredExchanges)) {
            $this->declareAmqpExchange($name);
        }

        // declare queue
        if (!in_array($name, $this->declaredQueues)) {
            $this->declareAmqpQueue(
                $name,
                [
                    'x-dead-letter-exchange' => $destinationExchange,
                    'x-dead-letter-routing-key' => $destination,
                    'x-message-ttl' => $delay * 1000,
                ]
            );
        }

        // bind queue to the exchange
        $this->declaredQueue->bind($exchange, $name);

        return [$name, $exchange];
    }

    /**
     * Retrieves the correlation id, or a unique id.
     *
     * @return string
     */
    public function getCorrelationId()
    {
        return $this->correlationId ?: uniqid('amqp', true);
    }

    /**
     * Sets the correlation id for a message to be published.
     *
     * @param string $id
     *
     * @return void
     */
    public function setCorrelationId($id)
    {
        $this->correlationId = $id;
    }

    /**
     * @param $message
     * @return string
     */
    protected function serializeMessage($payload)
    {
        return \json_encode($payload);
    }

    /**
     * @param string $action
     * @param Exception $e
     */
    private function reportConnectionError($action, Exception $e)
    {
        AppLog::error('AMQP error while attempting ' . $action . ': ' . $e->getMessage());
        // Sleep so that we don't flood the log file
        sleep($this->sleepOnError);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param \DateTime|int $delay
     * @param string $job
     * @param mixed $data
     * @param string $queue
     *
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue, ['delay' => $this->getSeconds($delay)]);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string|null $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueueName($queue);

        try {
            // declare queue if not exists
            $this->declareQueue($queue);

            // get envelope
            $message = $this->declaredQueue->get();

            if ($message instanceof AMQPEnvelope) {
                return new RabbitMQJob($this->container, $this, $this->channel, $this->declaredQueue, $message);
            }
        } catch (ErrorException $exception) {
            $this->reportConnectionError('pop', $exception);
        }

        return null;
    }

    /**
     * Sets the attempts member variable to be used in message generation.
     *
     * @param int $count
     *
     * @return void
     */
    public function setAttempts($count)
    {
        $this->attempts = $count;
    }
}
