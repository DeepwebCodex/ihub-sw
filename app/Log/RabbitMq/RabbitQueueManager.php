<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/11/16
 * Time: 2:23 PM
 */

namespace App\Log\RabbitMq;


use PhpAmqpLib\Channel\AMQPChannel;

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

    public function __construct(array $queueList)
    {
        if($queueList){
            foreach ($queueList as $queue){
                $this->queueList[] = new RabbitQueue($queue);
            }
        }
    }

    public function setUpQueue(AMQPChannel $channel, $prefix, $default_exchange)
    {
        if(!empty($this->queueList)){

            //$channel->exchange_declare($prefix . $default_exchange, 'direct', false, true, false);

            foreach ($this->queueList as $queue)
            {
                /**@var RabbitQueue $queue*/

                $channel->queue_declare($prefix . $queue->queue, $queue->passive, $queue->durable, $queue->exclusive, false);

                if($queue->event_levels) {
                    foreach ($queue->event_levels as $event_level) {
                        $channel->queue_bind($prefix . $queue->queue, $prefix . $default_exchange, $event_level);
                    }
                }
            }
        }
    }
}