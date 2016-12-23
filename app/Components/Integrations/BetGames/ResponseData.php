<?php

namespace App\Components\Integrations\BetGames;

/**
 * Class ResponseData
 * @package App\Components\Integrations\BetGames
 */
class ResponseData
{
    const TIME_TO_DISCONNECT = 10;

    private $method;
    private $token;
    private $params;

    /**
     * @var Error
     */
    private $error;

    /**
     * ResponseData constructor.
     * @param string $method
     * @param string $token
     * @param array $params
     * @param array|null $error
     */
    public function __construct(string $method = '', string $token = '', $params = [], array $error = null)
    {
        $this->method = $method;
        $this->token = $token;
        $this->params = $params;
        $this->error = $error ?? CodeMapping::getByErrorCode(StatusCode::OK);

    }

    /**
     * @return array
     */
    public function ok():array 
    {
        $view = [
            'method' => $this->method,
            'token' => $this->token,
            'success' => 1,
            'error_code' => $this->error['code'],
            'error_text' => $this->error['message'],
            'time' => time(),
            'params' => $this->params
        ];
        $this->setSignature($view);

        return $view;
    }

    /**
     * @return array
     */
    public function fail():array 
    {
        $view = [
            'method' => $this->method,
            'token' => $this->token,
            'success' => 0,
            'error_code' => $this->error['code'],
            'error_text' => $this->error['message'],
            'time' => time(),
        ];
        $this->setSignature($view);

        return $view;
    }

    public function wrong()
    {
        sleep(self::TIME_TO_DISCONNECT);
    }

    /**
     * @param array $data
     */
    private function setSignature(array &$data)
    {
        $sign = new Signature($data);
        $data['signature'] = $sign->getHash();
    }
}