<?php

namespace App\Components\Integrations\MicroGaming\Orion;

class CompleteGameProcessor implements IOperationsProcessor
{

    protected $bar;

    function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function make(array $data): array
    {
        if ($this->bar) {
            foreach ($data as $key) {
                $this->bar->advance();
            }
        }
        return $data;
    }

}
