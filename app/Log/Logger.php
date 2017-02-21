<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/11/16
 * Time: 6:08 PM
 */

namespace App\Log;


use AMQPConnection;
use App\Log\RabbitMq\Formatter\RabbitFormatter;
use App\Log\RabbitMq\RabbitHandler;
use App\Log\RabbitMq\RabbitQueueManager;
use App\Log\File\FileLogger;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Monolog\Handler\MongoDBHandler;

class Logger
{
    private $drivers = [
        'mongo' => [
            'driver'    => 'Mongo',
            'fallback'  => 'File'
        ],
        'rabbit' => [
            'driver'    => 'Rabbit',
            'fallback'  => 'File'
        ],
        'file'   => [
            'driver'    => 'File',
            'fallback'  => 'EchoLog'
        ],
    ];

    public function __construct(array $config, \Monolog\Logger $monolog, Application $app)
    {
        return $this->startLogDriver($config, $monolog, $app);
    }

    protected function startLogDriver(array $config, \Monolog\Logger $monolog, Application $app, $useFallback = false)
    {
        $driver = Arr::get($config, 'default');

        try
        {
            if(method_exists($this, 'run'.  Arr::get($this->drivers, $driver.'.driver')) && !$useFallback)
            {
                return $this->{'run'. $this->drivers[$driver]['driver']}(Arr::get($config, 'connections.' . $driver), $monolog, $app);
            } elseif(method_exists($this, 'run'.  Arr::get($this->drivers, $driver .'.fallback'))) {
                return $this->{'run'. $this->drivers[$driver]['fallback']}(Arr::get($config, 'connections.' . $driver), $monolog, $app);
            }
        }
        catch (\Exception $e)
        {
            if(!$useFallback) {
                return $this->startLogDriver($config, $monolog, $app, true);
            }

            throw $e;
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
            new AMQPConnection([
                'host'  => $config['host'],
                'port'  => $config['port'],
                'vhost' => '/',
                'login' => $config['user'],
                'password' => $config['password']
            ]),
            new RabbitQueueManager($config['queueList']),
            $config['prefix'],
            $config['default_exchange']
        );

        $rabbitHandler->setFormatter(new RabbitFormatter());

        /** @var \Monolog\Logger $monolog */
        $monolog->pushHandler($rabbitHandler);
    }

    public function runEchoLog()
    {
        return null;
    }

    public function runFile(array $config, \Monolog\Logger $monolog, $app){
        return (new FileLogger())->bootLogger($app, $monolog);
    }
}