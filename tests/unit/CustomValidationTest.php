<?php

use App\Components\Integrations\Casino\CasinoHelper;
use App\Components\Integrations\EuroGamesTech\DefenceCode;
use App\Http\Requests\Validation\CasinoValidation;
use App\Http\Requests\Validation\MicroGamingValidation;
use Codeception\Util\Stub;

class CustomValidationTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;

    protected function _before() {

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

    public function testMicroGamingValidationToken()
    {
        $token = "e4fda8473f68894a11c99acc25ecca11";

        $this->specify("Check validate token method", function() use($token) {
            verify("Validation passes", (new MicroGamingValidation)->validateToken(null, $token, null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionMessage("Token field is empty");
            (new MicroGamingValidation)->validateToken(null, '', null, null);
        });
    }


    public function testMicroGamingValidationPlayType()
    {
        $this->specify("Check validate play type method", function() {
            verify("Validation passes", (new MicroGamingValidation)->validatePlayType(null, 'bet', null, null))->true();

            $this->expectException(\App\Exceptions\Api\ApiHttpException::class);
            $this->expectExceptionMessage("Playtype 'gogobugs' is not implemented");
            (new MicroGamingValidation)->validatePlayType(null, 'gogobugs', null, null);
        });
    }
}