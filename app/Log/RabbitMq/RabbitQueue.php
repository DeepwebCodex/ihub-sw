<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/11/16
 * Time: 2:50 PM
 */

namespace App\Log\RabbitMq;

/**
 * @property string $queue
 * @property boolean $passive
 * @property boolean $durable
 * @property boolean $exclusive
 * @property array $event_levels
 */
class RabbitQueue
{
    private $attributes = [];

    public function __construct(array $data)
    {
        $this->attributes = array_get($data, 'queue_params', []);
        $this->attributes = array_merge($this->attributes, ['event_levels' => array_get($data, 'event_levels', [])]);
    }

    public function __get($name)
    {
        return array_get($this->attributes, $name);
    }

    public function canProcessLevel($level){
        return in_array($level, $this->event_levels);
    }
}