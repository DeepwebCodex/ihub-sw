<?php

namespace App\Queue\Jobs;

use AMQPChannel;
use AMQPEnvelope;
use AMQPQueue;
use App\Queue\RabbitMQQueue;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;

/**
 * Class RabbitMQJob
 * @package App\Queue\Jobs
 */
class RabbitMQJob extends Job implements JobContract
{
    /**
     * Same as RabbitMQQueue, used for attempt counts.
     */
    const ATTEMPT_COUNT_HEADERS_KEY = 'attempts_count';

    /**
     * @var RabbitMQQueue
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var AMQPQueue
     */
    protected $queue;

    /**
     * @var AMQPEnvelope
     */
    protected $message;

    protected $messageData;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Creates a new instance of RabbitMQJob.
     *
     * @param \Illuminate\Container\Container $container
     * @param \App\Queue\RabbitMQQueue $connection
     * @param \AMQPChannel $channel
     * @param AMQPQueue $queue
     * @param \AMQPEnvelope $message
     */
    public function __construct(
        Container $container,
        RabbitMQQueue $connection,
        AMQPChannel $channel,
        AMQPQueue $queue,
        AMQPEnvelope $message
    ) {
        $this->container = $container;
        $this->connection = $connection;
        $this->channel = $channel;
        $this->queue = $queue;
        $this->message = $message;

        $this->messageData = \json_decode($this->getRawBody(), true);
    }

    /**
     * Release the job back into the queue.
     *
     * @param int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        $this->delete();
        $this->setAttempts($this->attempts() + 1);

        $body = $this->payload();

        /*
         * Some jobs don't have the command set, so fall back to just sending it the job name string
         */
        if (isset($body['data']['command']) === true) {
            $job = \unserialize($body['data']['command']);
        } else {
            $job = $this->getName();
        }

        $data = $body['data'];

        if ($delay > 0) {
            $this->connection->later($delay, $job, $data, $this->getQueue());
        } else {
            $this->connection->push($job, $data, $this->getQueue());
        }
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();
        $this->queue->ack($this->message->getDeliveryTag());
    }

    /**
     * Sets the count of attempts at processing this job.
     *
     * @param int $count
     *
     * @return void
     */
    private function setAttempts($count)
    {
        $this->connection->setAttempts($count);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        if (array_has($this->messageData, 'application_headers') === true) {
            $headers = array_get($this->messageData, 'application_headers');

            if (isset($headers[self::ATTEMPT_COUNT_HEADERS_KEY]) === true) {
                return $headers[self::ATTEMPT_COUNT_HEADERS_KEY];
            }
        }

        // set default job attempts to 1 so that jobs can run without retry
        return 1;
    }

    /**
     * @return array
     */
    public function payload()
    {
        if (!$this->payload) {
            $this->payload = \json_decode($this->messageData['payload'], true);
        }
        return $this->payload;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->message->getBody();
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return $this->queue->getName();
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return array_get($this->messageData, 'correlation_id');
    }

    /**
     * Sets the job identifier.
     *
     * @param string $id
     *
     * @return void
     */
    public function setJobId($id)
    {
        $this->connection->setCorrelationId($id);
    }
}
