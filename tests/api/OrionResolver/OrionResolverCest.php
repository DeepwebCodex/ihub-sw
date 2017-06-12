<?php

namespace api\OrionResolver;

use ApiTester;
use Illuminate\Support\Facades\Artisan;
use Orion\TestData;
use Testing\Accounting\AccountManagerMock;
use Testing\Accounting\Params;

class OrionResolverCest
{

    public $testData;

    function __construct()
    {
        $this->params = new Params();
        $this->testData = new TestData($this->params);
    }

    public function testCommandCommit(ApiTester $I)
    {
        Artisan::call('orion:commit');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }

    public function testCommandRollback(ApiTester $I)
    {
        Artisan::call('orion:rollback');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }

    public function testCommandEndGame(ApiTester $I)
    {
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }

    public function testCommandFailedEndGame(ApiTester $I)
    {
        $this->testData->initMockCommandFailedEndGame($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertContains('Something went wrong!', trim($output));
    }

    public function testEndGameThrownException(ApiTester $I)
    {
        $this->testData->initMockEndGameThrownException($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertContains('Something went wrong!', trim($output));
    }

    public function testCommitWhenLostRowId(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->mock($I);
        $this->testData->initMockWhenLostRowId($I);
        Artisan::call('orion:commit');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }
    
    public function testEndGameWhenRowIdWithMinus(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->mock($I);
        $this->testData->initMockEndGameWhenRowIdWithMinus($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }
    
    public function testCommitWithoutBet(ApiTester $I)
    {
        (new AccountManagerMock($this->params))
            ->userInfo()
            ->getFreeOperationId(123)
            ->mock($I);
        $this->testData->initMockCommitWithoutBet($I);
        Artisan::call('orion:commit');
        $output = Artisan::output();
        $I->assertContains('Success.', trim($output));
    }

}
