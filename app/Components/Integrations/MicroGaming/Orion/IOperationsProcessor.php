<?php

namespace App\Components\Integrations\MicroGaming\Orion;

interface IOperationsProcessor
{

    public function make(array $data): array;

    public function setBar($bar);
}
