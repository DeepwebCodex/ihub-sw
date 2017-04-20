<?php

use App\Http\Requests\Validation\MicroGamingValidation;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

class CustomValidationTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;

    protected function _before() {

    }

    protected function _after() {}

    public function testMicroGamingValidationToken()
    {
        $token = "e4fda8473f68894a11c99acc25ecca11";

        $this->specify("Check validate token method", function() use($token) {
            verify("Validation passes", (new MicroGamingValidation)->validateToken(null, $token, null, null))->true();

            $this->expectException(ApiHttpException::class);
            $this->expectExceptionMessage("Token field is empty");
            (new MicroGamingValidation)->validateToken(null, '', null, null);
        });
    }


    public function testMicroGamingValidationPlayType()
    {
        $this->specify("Check validate play type method", function() {
            verify("Validation passes", (new MicroGamingValidation)->validatePlayType(null, 'bet', null, null))->true();

            $this->expectException(ApiHttpException::class);
            $this->expectExceptionMessage("Playtype 'gogobugs' is not implemented");
            (new MicroGamingValidation)->validatePlayType(null, 'gogobugs', null, null);
        });
    }
}