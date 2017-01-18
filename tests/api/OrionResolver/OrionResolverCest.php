<?php

use Illuminate\Support\Facades\Artisan;

class OrionResolverCest {

    public function testCommandCommit(ApiTester $I) {
        $I->callArtisan('orion:commit');
        $outpat = Artisan::output();
    }

}
