<?php


namespace App\Components\Integrations\Fundist;


class Hmac
{
    private $hmac;
    private $forCheck;
    private $integration;

    public function __construct(array $packet, string $integration, string $hmac = null)
    {
        if (!$integration) {
            throw new \Exception("integration for Hmac not set");
        }
        $this->integration = $integration;
        $this->create($packet);
        $this->forCheck = $hmac;
    }

    public function get():string
    {
        return $this->hmac;
    }

    public function isCorrect()
    {
        return $this->hmac === $this->forCheck;
    }

    /**
     * @param array $packet
     */
    private function create(array $packet)
    {
        ksort($packet);

        $str = "";
        foreach ($packet as $key => $value) {
            if (!is_array($value) && !is_object($value)) {
                $str .= $value;
            }
        }

        $this->hmac = hash_hmac('sha256', $str, hash('sha256', config('integrations.' . $this->integration . '.secret_word'), true));
    }
}