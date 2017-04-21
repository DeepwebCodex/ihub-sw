<?php

namespace unit\Components\Exceptions;

use App\Http\Middleware\IPList;

class CheckIPCest
{

    public function __construct()
    {
        $this->middleware = new IPList();
    }

    public function testWrongTypeIP(\UnitTester $I)
    {
        config(['integrations' => ['egt' =>['allowed_ips' => 123]]]);
        $I->assertFalse($this->middleware->isValidIP(123, 'egt'));
    }

    public function testRightIP(\UnitTester $I)
    {
        config(['integrations' => ['egt' =>['allowed_ips' => '123']]]);
        $I->assertTrue($this->middleware->isValidIP('123', 'egt'));
    }

    public function testWrongIP(\UnitTester $I)
    {
        config(['integrations' => ['egt' =>['allowed_ips' => '123']]]);
        $I->assertFalse($this->middleware->isValidIP('456', 'egt'));
    }

    public function testEmptyIP(\UnitTester $I)
    {
        config(['integrations' => ['egt' =>['allowed_ips' => '']]]);
        $I->assertTrue($this->middleware->isValidIP('123', 'egt'));
    }

    public function testArrayIP(\UnitTester $I)
    {
        config(['integrations' => ['egt' =>['allowed_ips' => ['123', '456']]]]);
        $I->assertTrue($this->middleware->isValidIP('123', 'egt'));
        $I->assertTrue($this->middleware->isValidIP('456', 'egt'));
    }

}