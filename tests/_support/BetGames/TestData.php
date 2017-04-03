<?php

namespace BetGames;

use App\Components\Users\IntegrationUser;
use App\Components\Integrations\BetGames\Signature;
use Testing\Params;
use GuzzleHttp\Psr7\Response;

class TestData
{
    const IS_MOCK = true;
    const AMOUNT = 10;
    /**
     * @var IntegrationUser
     */
    private $userId;
    private $currency;
    private $amount;
    private $amount_backup;
    public $bigAmount;

    public function __construct()
    {
//        $this->amount = self::AMOUNT;
        $this->userId = (int)env('TEST_USER_ID');
        $this->currency = Params::CURRENCY;
        $this->amount_backup =
        $this->amount = Params::AMOUNT * 100;
        $this->bigAmount = Params::BIG_AMOUNT * 100;
    }

    public function notFound()
    {
        return $this->basic('not_found');
    }

    public function ping()
    {
        $data = [
            'method' => 'ping',
            'token' => '-',
            'time' => time(),
            'params' => null,
        ];

        return $this->setSignature($data);
    }

    public function token()
    {
        $data = [
            'method' => 'request_new_token',
            'token' => $this->getIcmsToken(),
            'time' => time(),
            'params' => null,
        ];
        $data = $this->setSignature($data);

        return $data;
    }

    public function tokenData()
    {
        return  [
            "user_id" => $this->userId,
            "partner_id" => 1,
            "game_id" => 1,
            "currency" => $this->currency,
            "unique_id" => time(),
            "cashdesk_id" => -5
        ];
    }

    public function getIcmsToken()
    {
        $data = [
            "partner_id" => 1,
            "user_id" => $this->userId,
            "cashdesk_id" => -5
        ];

        /**@var Response $response*/
        $response = app('Guzzle')::post('http://' . $this->getIcmsServer() . '/internal/v2/bg/generateToken',
            ['json' => $data]
        );

        return json_decode((string)$response->getBody(), true)['token'];
    }

    private function getIcmsServer()
    {
        switch (env('APP_URL')) {
            case 'http://ihub.dev' :
                return 'icms.dev:8181';
            case 'http://ihub.favbet.dev' :
                return 'ihub.favbet.dev:8180';
            default :
                throw new \Exception('APP_URL not found');
        }
    }

    public function authFailed()
    {
        $data = [
            'method' => 'get_account_details',
            'token' => 'authorization_must_fails',
            'time' => time(),
            'params' => [],
        ];
        $data = $this->setSignature($data);

        return $data;
    }

    public function account()
    {
        return $this->basic('get_account_details');
    }

    public function refreshToken()
    {
        return $this->basic('refresh_token');
    }

    public function newToken()
    {
        return $this->basic('request_new_token');
    }

    public function getBalance()
    {
        return $this->basic('get_balance');
    }

    public function bet($bet_id = null, $trans_id = null)
    {
        return $this->transaction('transaction_bet_payin', $bet_id, $trans_id);
    }

    public function win($bet_id = null, $trans_id = null)
    {
        return $this->transaction('transaction_bet_payout', $bet_id, $trans_id, $this->userId);
    }

    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function resetAmount()
    {
        return $this->amount = $this->amount_backup;
    }

    public function wrongTime($method, $params = null)
    {
        $token = Token::create($this->userId, $this->currency);
        $data = [
            'method' => $method,
            'token' => $token->get(),
            'time' => 12345,
            'params' => $params,
        ];
        $data = $this->setSignature($data);

        return $data;
    }

    private function basic($method, $params = null)
    {
        $data = [
            'method' => $method,
            'token' => (string)(microtime(true) * 10000) . 'Qwe',
            'time' => time(),
            'params' => $params,
        ];

        return $this->setSignature($data);
    }

    private function transaction($method, $bet_id = null, $trans_id = null, $player_id = null)
    {
        $params = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'bet_id' => $bet_id ?? $this->getObjectId(),
            'transaction_id' => $trans_id ?? $this->getObjectId(), //md5(str_random()),
            'retrying' => 0,
            'game' => gen_uid(),
        ];
        if ($player_id) {
            $params['player_id'] = $player_id;
        }
        return $this->basic($method, $params);
    }

    private function setSignature($input)
    {
        $data = $input;
        $data['signature'] = (new Signature($input))->getHash();

        return $data;
    }

    public function updateSignature($input)
    {
        unset($input['signature']);
        $data = $input;
        $data['signature'] = (new Signature($input))->getHash();
        return $data;
    }

    private function getObjectId()
    {
        return (self::IS_MOCK) ? Params::OBJECT_ID : time() + mt_rand(1, 10000);
    }
}