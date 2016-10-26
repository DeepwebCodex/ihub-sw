<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/11/16
 * Time: 6:08 PM
 */

namespace App\Log;


use App\Log\RabbitMq\Formatter\RabbitFormatter;
use App\Log\RabbitMq\RabbitHandler;
use App\Log\RabbitMq\RabbitQueueManager;
use App\Log\File\FileLogger;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\MongoDBHandler;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Logger
{
    private $drivers = [
        'mongo' => 'Mongo',
        'rabbit' => 'Rabbit',
        'file'   => 'File'
    ];

    public function __construct(array $config, \Monolog\Logger $monolog, Application $app)
    {
        $driver = Arr::get($config, 'default');

        if(method_exists($this, 'run'.  Arr::get($this->drivers, $driver)))
        {
            return $this->{'run'. $this->drivers[$driver]}(Arr::get($config, 'connections.' . $driver), $monolog, $app);
        }
    }

    public function runMongo(array $config, \Monolog\Logger $monolog, $app)
    {
        if (!$config['server']) {
            return;
        }

        $mongoHandler = new MongoDBHandler(
            new \MongoDB\Client($config['server']),
            $config['db_name'],
            $config['collection_name']
        );

        $mongoHandler->setFormatter(new \App\Log\Monolog\Formatter\AppFormatter());

        /** @var \Monolog\Logger $monolog */
        $monolog->pushHandler($mongoHandler);
    }

    public function runRabbit(array $config, \Monolog\Logger $monolog, $app)
    {
        if(!$config['host'] || !$config['port']){
            return;
        }

        $rabbitHandler = new RabbitHandler(
            new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['password']),
            new RabbitQueueManager($config['queueList']),
            $config['prefix'],
            $config['default_exchange']
        );

        $rabbitHandler->setFormatter(new RabbitFormatter());

        /** @var \Monolog\Logger $monolog */
        $monolog->pushHandler($rabbitHandler);
    }

    public function runFile(array $config, \Monolog\Logger $monolog, $app){
        return (new FileLogger())->bootLogger($app, $monolog);
    }
}