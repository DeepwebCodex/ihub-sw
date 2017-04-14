<?php

namespace App\Components\Integrations\BetGames;

use App\Components\Traits\MetaDataTrait;

class Signature
{
    use MetaDataTrait;

    private $hash;
    private $partnerId;
    private $cashdeskId;

    /**
     * Signature constructor.
     *
     * @param array $data
     * @param $partnerId
     * @param $cashdeskId
     */
    public function __construct(array $data, $partnerId, $cashdeskId)
    {
        $this->partnerId = $partnerId;
        $this->cashdeskId = $cashdeskId;

        $this->hash = $this->create($data);
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    private function create($data): string
    {
        $result = '';
        foreach ($data as $key => $value) {
            if (($key === 'params' && empty($value)) || $key === 'signature' || $key === $this->metaStorageKey) {
                continue;
            } elseif ($key === 'params' && !empty($value)) {
                foreach ($value as $keyParam => $param) {
                    $result .= $keyParam . $param;
                }
            } else {
                $result .= $key . $value;
            }
        }
        $result .= config(
            "integrations.betGames.secret_separated.{$this->partnerId}.{$this->cashdeskId}",
            config('integrations.betGames.secret')
        );

        return md5($result);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function isWrong(string $value): bool
    {
        return $value != $this->hash;
    }
}