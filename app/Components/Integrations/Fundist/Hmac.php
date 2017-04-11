<?php


namespace App\Components\Integrations\Fundist;


class Hmac
{
    public static $INTEGRATION;

    private $hmac;
    private $forCheck;

    public function __construct(array $packet, string $hmac = null)
    {
        if (!self::$INTEGRATION) {
            throw new \Exception("integration for Hmac not set");
        }
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

        $this->hmac = hash_hmac('sha256', $str, hash('sha256', config('integrations.' . self::$INTEGRATION . '.secret_word'), true));
    }
}