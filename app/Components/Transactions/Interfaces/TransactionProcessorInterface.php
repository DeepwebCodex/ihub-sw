<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/12/16
 * Time: 6:03 PM
 */

namespace App\Components\Transactions\Interfaces;


use App\Components\Transactions\TransactionRequest;

interface TransactionProcessorInterface
{
    /**
     * @param TransactionRequest $request
     * @return array
     */
    public function process(TransactionRequest $request);


    /**
     * @return array
     */
    public function getTransactionData();

    /**
     * @return bool
     */
    public function isDuplicate();
}