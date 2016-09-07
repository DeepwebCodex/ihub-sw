<?php

namespace App\Components\ExternalServices;


use App\Exceptions\Api\ApiHttpException;


class RemoteSession
{
    private $remoteHost;
    private $remotePort;

    private $session_prefix;
    private $lifetime;

    private $memCache;

    private $sessionData;
    private $sessionId;

    public function __construct($session_id)
    {
        $this->remoteHost = config('external.hazlecast_sessions.host');
        $this->remotePort = config('external.hazlecast_sessions.port');

        $this->setUp();

        $this->reStart($session_id);
    }

    private function setUp()
    {
        $this->memCache = new \Memcache();

        $this->session_prefix = ini_get("memcached.sess_prefix");
        $this->lifetime = ini_get("session.gc_maxlifetime");

        try {
            $this->memCache->pconnect($this->remoteHost, $this->remotePort);
        } catch (\Exception $e) {
            throw new ApiHttpException(503, "Service unavailable");
        }
    }

    public function reStart($session_id)
    {
        $this->sessionData = null;

        $rawData = $this->memCache->get($this->session_prefix . $session_id);

        $this->sessionId = $session_id;

        $this->sessionData = $this->_unserialize($rawData);
    }

    /**
     * Get data from remote session - supports laravel dot notation
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return array_get($this->sessionData, $key, null);
    }

    /**
     * Replaces session data with provided array
     *
     * @param array $data
     * @return bool
     */
    public function set(array $data)
    {
        if (!(is_array($this->sessionData) && $this->sessionId)) {
            return false;
        }

        $dataToSave = array_merge($this->sessionData, $data);

        try {
            return $this->memCache->set($this->session_prefix . $this->sessionId, $this->_serialize($dataToSave), false, $this->lifetime);
        } catch (\Exception $e) {
            throw new ApiHttpException(503, "Service unavailable");
        }
    }

    /**
     * Key exists in remote session - supports laravel dot notation
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key)
    {
        return (bool)array_get($this->sessionData, $key, false);
    }

    public function getSessionId()
    {
        return $this->sessionId ?: false;
    }

    /**
     * Unserialize
     *
     * This function unserializes a data string, then converts any
     * temporary slash markers back to actual slashes
     *
     * @access    private
     * @param    array
     * @return    string
     */
    private function _unserialize($data)
    {
        $data = @unserialize($this->strip_slashes($data));

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('{{slash}}', '\\', $val);
                }
            }

            return $data;
        }

        return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
    }

    /**
     * Serialize an array
     *
     * This function first converts any slashes found in the array to a temporary
     * marker, so when it gets unserialized the slashes will be preserved
     *
     * @access    private
     * @param    array
     * @return    string
     */
    private function _serialize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_string($val)) {
                    $data[$key] = str_replace('\\', '{{slash}}', $val);
                }
            }
        } else {
            if (is_string($data)) {
                $data = str_replace('\\', '{{slash}}', $data);
            }
        }

        return serialize($data);
    }

    private function strip_slashes($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $val) {
                $str[$key] = $this->strip_slashes($val);
            }
        } else {
            $str = stripslashes($str);
        }

        return $str;
    }
}