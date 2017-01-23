<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Integrations\MicroGaming\Orion;

/**
 *
 * @author petroff
 */
interface IOperationsProcessor {

    public function make(array $data): array;
    
}
