<?php

namespace App\Components\Integrations\VirtualSports;

use Illuminate\Support\Facades\Redis;

/**
 * Class Calculator
 * @package App\Components\Integrations\VirtualSports
 */
class Calculator
{
    /**
     * @param $eventId
     * @return string
     * @throws \RuntimeException
     */
    public static function sendMessageApprove($eventId): string
    {
        $routingKey = 'calc';

        //TODO::separate redis connection for prod
        $val = Redis::get('calc_event:' . $eventId);
        if ($val !== 'calc_inprogress') {
            $exchange = 'calculator';
            $msg = ['events' => [$eventId]];

            $response = app('AmqpService')->sendMsg($exchange, $routingKey, $msg);

            if ($response === true) {
                return 'ok';
            }
            return 'NotResponse';
        }
        return 'Event now calc!';
    }
}