<?php


namespace App\Components\Integrations\NetEntertainment;


class Hmac
{
    private $hmac;
    private $forCheck;

    public function __construct(array $packet, string $hmac = null)
    {
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

        $this->hmac = hash_hmac('sha256', $str, hash('sha256', config('integrations.netEnt.secret_word'), true));
    }
}