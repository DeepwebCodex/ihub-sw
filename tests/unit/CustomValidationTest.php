<?php

use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\EuroGamesTech\DefenceCode;
use App\Http\Requests\Validation\CasinoValidation;
use App\Http\Requests\Validation\MicroGamingValidation;
use Codeception\Util\Stub;

class CustomValidationTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;

    private $egtRequestData;
    private $egtValidatorStub;

    protected function _before() {
        $this->egtRequestData = [
            'PlayerId'  => random_int(10000, 40000),
            'PortalCode' => 'RUB'
        ];

        $this->egtRequestData['DefenceCode'] = (new DefenceCode)->generate($this->egtRequestData['PlayerId'], $this->egtRequestData['PortalCode'], time());

        $this->egtValidatorStub = Stub::construct(\App\Http\Requests\Validation\EuroGamesTechValidation::class, [], [
            'getRequest' => Stub::atLeastOnce(function(){
                $request = request();
                $request->merge($this->egtRequestData);

                return $request;
            })
        ]);
    }

    protected function _after() {}

    public function testCasinoValidators()
    {
        $requestData = [
            'data'  => 'data',
            'data2' => 'data2'
        ];

        $stubbedValidator = Stub::make(CasinoValidation::class, [
            'getRequest' => Stub::atLeastOnce(function() use($requestData){
                $request = request();
                $request->merge($requestData);
                return $request;
            })
        ]);

        $this->specify("Check signature of a request", function() use($stubbedValidator, $requestData){
            $signature = CasinoHelper::generateActionSignature($requestData);

            verify("Validation passes", $stubbedValidator->CheckSignature(null, $signature, null, null))->true();
        });

        $this->specify("Amount for casino must always be > 0", function() use ($stubbedValidator){
            verify("Amount passes", $stubbedValidator->CheckAmount(null, 100, null, null))->true();
            verify("Amount check failed", $stubbedValidator->CheckAmount(null, -5, null, null))->false();
        });
    }

    public function testEuroGamesTechValidatorDefenceCode()
    {
        $this->specify("Check defence code of a request", function(){
            verify("Validation passes", $this->egtValidatorStub->checkDefenceCode(null, $this->egtRequestData['DefenceCode'], null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionCode(0);
            $this->egtValidatorStub->checkDefenceCode(null, 'dsfsdf-sdfsdf', null, null);
        });
    }

    public function testEuroGamesTechValidatorExpirationTime()
    {
        $this->specify("Check check expiration time", function() {
            verify("Validation passes", $this->egtValidatorStub->checkExpirationTime(null, $this->egtRequestData['DefenceCode'], null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionCode(0);
            $this->egtValidatorStub->checkDefenceCode(null, 'dsfsdf-13216486181685', null, null);
        });
    }

    public function testMicroGamingValidationToken()
    {
        $token = \App\Components\Integrations\MicroGaming\MicroGamingHelper::generateToken('DFGSED15LKUIY8741', 'WMZ');

        $this->specify("Check validate token method", function() use($token) {
            verify("Validation passes", MicroGamingValidation::validateToken(null, $token, null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionMessage("Token field is empty");
            MicroGamingValidation::validateToken(null, '', null, null);
        });
    }

    public function testMicroGamingValidationTime()
    {

        $token = \App\Components\Integrations\MicroGaming\MicroGamingHelper::generateToken('DFGSED15LKUIY8741', 'WMZ');

        $this->specify("Check validate time method", function() use($token) {
            verify("Validation passes", MicroGamingValidation::validateTime(null, $token, null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionCode(0);
            MicroGamingValidation::validateTime(null, '', null, null);
        });
    }

    public function testMicroGamingValidationPlayType()
    {
        $this->specify("Check validate play type method", function() {
            verify("Validation passes", MicroGamingValidation::validatePlayType(null, 'bet', null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionMessage("Playtype 'gogobugs' is not implemented");
            MicroGamingValidation::validatePlayType(null, 'gogobugs', null, null);
        });
    }
}