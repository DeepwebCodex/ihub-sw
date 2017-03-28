<?php

namespace api\OrionResolver;

use ApiTester;
use App\Components\ExternalServices\MicroGaming\Orion\SoapEmulator;
use Illuminate\Support\Facades\Artisan;
use Mockery;

class OrionResolverCest
{

    private function initMock(ApiTester $I)
    {
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->andReturn('<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><s:Fault
><faultcode xmlns:a="http://schemas.microsoft.com/ws/2005/05/addressing/none">a:
ActionNotSupported</faultcode><faultstring xml:lang="en-US">The message with Act
ion "http://mgsops.net/AdminAPI_Admin/IVanguardAdmin2/GetFailedEndGameQueue" can
not be processed at the receiver, due to a ContractFilter mismatch at the Endpoi
ntDispatcher. This may be because of either a contract mismatch (mismatched Acti
ons between sender and receiver) or a binding/security mismatch between the send
er and the receiver.  Check that sender and receiver have the same contract and
the same binding (including security requirements, e.g. Message, Transport, None
).</faultstring></s:Fault></s:Body></s:Envelope>');
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
    }
    
    private function initMock2(ApiTester $I)
    {
        $className = SoapEmulator::class;
        $mock = Mockery::mock($className);
        $mock->shouldReceive('sendRequest')->andThrow(new \Exception('Ecxeption unknown'), 'Thrown exception');
        $I->getApplication()->instance($className, $mock);
        $I->haveInstance($className, $mock);
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
        $this->initMock($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Something went wrong!', trim($output));
    }
    
    public function testEndGameThrownException(ApiTester $I)
    {
        $this->initMock2($I);
        Artisan::call('orion:endgame');
        $output = Artisan::output();
        $I->assertEquals('Something went wrong!', trim($output));
    }

}
