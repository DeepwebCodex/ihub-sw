<?php

namespace api\OrionResolver;

use ApiTester;
use Illuminate\Support\Facades\Artisan;
use Orion\TestData;

class OrionResolverCest
{

    public $testData;

    function __construct()
    {
        $this->testData = new TestData();
    }

    public function testCommandCommit(ApiTester $I)
    {
        Artisan::call('orion:commit');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

    public function testCommandRollback(ApiTester $I)
    {
        Artisan::call('orion:rollback');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

    public function testCommandEndGame(ApiTester $I)
    {
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

    public function testCommandFailedEndGame(ApiTester $I)
    {
        $this->testData->initMock($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Something went wrong!', trim($output));
    }

    public function testEndGameThrownException(ApiTester $I)
    {
        $this->testData->initMock2($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Something went wrong!', trim($output));
    }

}
