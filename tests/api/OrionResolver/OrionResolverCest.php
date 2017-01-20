<?php

use Illuminate\Support\Facades\Artisan;

class OrionResolverCest {

    public function testCommandCommit(ApiTester $I) {
        Artisan::call('orion:commit');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

    public function testCommandRollback(ApiTester $I) {
        Artisan::call('orion:rollback');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

    public function testCommandEndGame(ApiTester $I) {
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Success.', trim($output));
    }

}
