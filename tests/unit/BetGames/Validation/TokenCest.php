<?php

namespace unit\BetGames\Validation;


use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use App\Http\Requests\Validation\BetGamesValidation;

class TokenCest
{
    /**
     * @var BetGamesValidation
     */
    private $validation;

    public function __construct()
    {
        $this->validation = new BetGamesValidation();
    }

    public function testToken(\UnitTester $I)
    {
        $token = "e4fda8473f68894a11c99acc25ecca11@%%$$";

        $I->assertTrue($this->checkToken($token));

        $I->expectException(ApiHttpException::class,  function() {
            $this->checkToken('123');
        });
    }

    public function testNoLetters(\UnitTester $I)
    {
        $I->expectException(ApiHttpException::class,  function() {
            $this->checkToken('qwertyiopasdfghjkl');
        });
    }
    public function testNoDigital(\UnitTester $I)
    {
        $I->expectException(ApiHttpException::class,  function() {
            $this->checkToken('123456789012345');
        });
    }

    public function testWrongLength(\UnitTester $I)
    {
        $I->expectException(ApiHttpException::class,  function() {
            $this->checkToken('123AASDF');
        });
    }

    private function checkToken($token)
    {
        return $this->validation->checkToken(null, $token, null, null);
    }
}