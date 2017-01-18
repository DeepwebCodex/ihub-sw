<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands\Orion;

use App\Facades\AppLog;

/**
 * Description of Operation
 *
 * @author petroff
 */
trait Operation {

    public function handleError(string $message, string $module, string $line): int {
        //critical($message, $node = '', $module = '', $line = '')
        AppLog::critical($message, 'orion', $module, $line);
        $this->error('Something went wrong!');
        return -1;
    }

    public function handleSuccess(array $dataSuccess) {
        AppLog::info('Success. Data: ' . print_r($dataSuccess, true));
        $this->info('succes');
    }

}
