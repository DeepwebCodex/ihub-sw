<?php

namespace unit\BetGames\Components;


use App\Components\Integrations\BetGames\Signature;

class SignatureCest
{
    /**
     * Data from documentation
     *
     * @param \UnitTester $I
     */
    public function testPingSignature(\UnitTester $I)
    {
        $data = [
            'method' => 'ping',
            'token' => '-',
            'time' => 1423124660,
            'params' => null,
            'signature' => '6094dc0397895ee55c93b01f54477527'
        ];

        $this->setSecretConfig();
        $signature = (new Signature($data));
        $I->assertEquals($data['signature'], $signature->getHash());
    }

    /**
     * Data from documentation
     *
     * @param \UnitTester $I
     */
    public function testResponseSignature(\UnitTester $I)
    {
        $data = [
            'method' => 'get_account_details',
            'token' => 'c2696fe0-eba8-012f-596c-528c3f9e4820',
            'success' => 1,
            'error_code' => 0,
            'error_text' => null,
            'time' => 1423127764,
            'params' => [
                'user_id' => 150205,
                'username' => 'test_player',
                'currency' => 'eur',
                'info' => 'Vilnius, LT',
            ],
        ];

        $hash = 'ca9fd88a49f039f5bde952c31247f09a';

        $this->setSecretConfig();
        $signature = (new Signature($data));
        $I->assertEquals($hash, $signature->getHash());
    }


    /**
     * Data from documentation
     *
     * @param \UnitTester $I
     */
    public function testWrongParametersOrder(\UnitTester $I)
    {
        $data = [
            'token' => 'c2696fe0-eba8-012f-596c-528c3f9e4820',
            'method' => 'get_account_details',
            'success' => 1,
            'error_code' => 0,
            'error_text' => null,
            'time' => 1423127764,
            'params' => [
                'user_id' => 150205,
                'username' => 'test_player',
                'currency' => 'eur',
                'info' => 'Vilnius, LT',
            ],
        ];

        $hash = 'ca9fd88a49f039f5bde952c31247f09a';

        $this->setSecretConfig();
        $signature = (new Signature($data));
        $I->assertNotEquals($hash, $signature->getHash());
    }

    private function setSecretConfig()
    {
        config(['integrations' => ['betGames' => ['secret' => '1JD4U-S7XB6-GKITA-DQXHP']]]);
    }
}