<?php

namespace App\Providers;

use App\Queue\Connectors\RabbitMQConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

/**
 * Class QueueRabbitMQServiceProvider
 * @package App\Providers
 */
class QueueRabbitMQServiceProvider extends ServiceProvider
{
    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];
        $connector = new RabbitMQConnector;
        $queue->stopping(function () use ($connector) {
            $connector->connection()->disconnect();
        });
        $queue->addConnector('rabbitmq', function () use ($connector) {
            return $connector;
        });
    }
}
