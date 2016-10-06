<?php

namespace App\Http\Controllers\Internal;

use App\Facades\AppLog;
use App\Http\Controllers\Controller;
use App\Models\Erlybet\Integration\LiveDealerLink;

/**
 * Class LiveDealerController
 * @package App\Http\Controllers\Internal
 */
class LiveDealerController extends Controller
{
    const NODE = 'live_dealer';

    /**
     * Test transactions
     */
    public function checkTransactions()
    {
        try {
            $apiTransactions = app('LiveDealerApi')->getStatistics();
        } catch (\Exception $e) {
            AppLog::error($e->getMessage(), self::NODE);
        }

        if (empty($apiTransactions)) {
            AppLog::info('Processed transactions - 0', self::NODE);
            return;
        }

        $firstItem = reset($apiTransactions);
        $lastItem = end($apiTransactions);
        $transactions = (new LiveDealerLink())->getTransactions(
            $firstItem['game_id'],
            $firstItem['action_id'],
            $lastItem['game_id'],
            $lastItem['action_id']
        );


        $liveDealerCount = count($apiTransactions);
        $this->testEq('Count', $liveDealerCount, count($transactions));
        foreach ($apiTransactions as $key => $ld) {
            $this->testEq('Game', $ld['game_id'], $transactions[$key]['game_id']);
            $this->testEq('Action Id', $ld['action_id'], $transactions[$key]['i_actionid']);
            $this->testEq('Action Type', $ld['action_type'], $transactions[$key]['type']);
            $this->testEq('User', $ld['user'], $transactions[$key]['user_id']);
            $this->testEq('Currency', $ld['currency_name'], $transactions[$key]['currency']);
            $this->testEq('Amount', $ld['amount'], $transactions[$key]['amount']);
        }
        AppLog::info('Processed transactions - ' . $liveDealerCount, self::NODE);
    }

    /**
     * Run single test and log errors
     *
     * @param string $testName
     * @param mixed $testValue
     * @param mixed $expectedValue
     */
    private function testEq($testName, $testValue, $expectedValue)
    {
        if ($testValue != $expectedValue) {
            $errorMessage = "Checking failed: \n
                Test name: {$testName} \n
                Test value: {$testValue} \n
                Expected value: {$expectedValue}";

            AppLog::error($errorMessage, self::NODE);
        }
    }
}
