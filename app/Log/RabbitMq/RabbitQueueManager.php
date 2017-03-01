<?php

namespace App\Log\RabbitMq;


use AMQPChannel;
use AMQPExchange;
use AMQPQueue;

class RabbitQueueManager
{
    /**
     * List in format
     * $item = [
     *      'event_levels'   => [
     *          'error',
     *          'info',
     *          ......
     *      ],
     *      'queue_params'  => [
     *          'queue' => *name*,
     *          'passive' => *bool*,
     *          'durable' => *bool*,
     *          'exclusive' => *bool*
     *      ]
     *  ]
     */
    protected $queueList = [];
    protected $exchange;

    public function __construct(array $queueList)
    {
        if($queueList){
            foreach ($queueList as $queue){
                $this->queueList[] = new RabbitQueue($queue);
            }
        }
    }

    public function setUpQueue(AMQPChannel $channel, $prefix, $default_exchange) : AMQPExchange
    {
        $this->exchange = new AMQPExchange($channel);
        $this->exchange->setName($prefix . $default_exchange);
        $this->exchange->setType(AMQP_EX_TYPE_DIRECT);
        $this->exchange->setFlags(AMQP_DURABLE);
        $this->exchange->declareExchange();

        if(!empty($this->queueList)){
            foreach ($this->queueList as $queue)
            {
                $rQueue = new AMQPQueue($channel);
                $rQueue->setName($prefix . $queue->queue);

                $flags = AMQP_NOPARAM;

                if($queue->passive) {
                    $flags = $flags | AMQP_PASSIVE;
                }

                if($queue->durable) {
                    $flags = $flags | AMQP_DURABLE;
                }

                if($queue->exclusive) {
                    $flags = $flags | AMQP_EXCLUSIVE;
                }

                $rQueue->setFlags($flags);
                $rQueue->declareQueue();

                if($queue->event_levels) {
                    foreach ($queue->event_levels as $event_level) {
                        $rQueue->bind($prefix . $default_exchange, $event_level);
                    }
                }
            }
        }

        return $this->exchange;
    }
}