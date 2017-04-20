<?php

namespace App\Components\Integrations\VirtualSports\Interfaces;

interface EventResultInterface
{
    public function process() : int;

    public function finishEvent();
}