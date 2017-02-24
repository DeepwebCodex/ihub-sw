<?php

namespace App\Queue\Connectors;

use App\Queue\RabbitMQQueue;
use Illuminate\Queue\Connectors\ConnectorInterface;

/**
 * Class RabbitMQConnector
 * @package App\Queue\Connectors
 */
class RabbitMQConnector implements ConnectorInterface
{
    /** @var \AMQPConnection */
    private $connection;

    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $rabbitmqConfig = config('rabbitmq');
        $rabbitmqConfig['queue'] = $config['queue'];

        $connectionConfig = array_only($rabbitmqConfig, ['host', 'port', 'vhost', 'login', 'password']);

        // create connection with AMQP
        $connection = new \AMQPConnection($connectionConfig);

        return new RabbitMQQueue($connection, $rabbitmqConfig);
    }

    /**
     * @return \AMQPConnection
     */
    public function connection()
    {
        return $this->connection;
    }
}
