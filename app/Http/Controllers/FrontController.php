<?php

namespace App\Http\Controllers;

use Behat\Gherkin\Exception\NodeException;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Mockery\Exception;

class FrontController extends Controller
{
    public function index()
    {

        throw new NodeException();
        /*$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('log', false, true, false, false);

        $msg = new AMQPMessage(json_encode([
            'type' => 'message',
            'id' => random_int(0, 150000),
            'content' => [
                'body' => 'some text'
            ]
        ]), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $channel->basic_publish($msg, 'direct', 'log');

        $channel->close();
        $connection->close();*/

        /*$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('log', false, true, false, false);

        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
        };

        $channel->basic_consume('log', '', false, true, false, false, $callback);*/

        return view('welcome');
    }
}
