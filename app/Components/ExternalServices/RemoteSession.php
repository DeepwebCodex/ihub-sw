<?php

namespace App\Components\ExternalServices;


use App\Exceptions\Api\ApiHttpException;
use App\Facades\AppLog;


class RemoteSession
{
    private $remoteHost;
    private $remotePort;

    private $session_prefix;
    private $lifetime;

    /** @var  \Memcached $memCache */
    private $memCache;

    private $sessionData;
    private $sessionId;

    /**
     * RemoteSession constructor.
     * @param bool $session_id
     */
    public function __construct($session_id = false)
    {
        $this->remoteHost = config('external.hazlecast_sessions.host');
        $this->remotePort = config('external.hazlecast_sessions.port');

        $this->sessionId = $session_id;
    }

    public function setUp()
    {
        $this->memCache = new \Memcached('sessions_pool');

        $this->session_prefix = ini_get("memcached.sess_prefix"); // 'memc.sess.key.' - for dev server
        $this->lifetime = ini_get("session.gc_maxlifetime");

        try {
            $this->memCache->addServer($this->remoteHost, $this->remotePort);
        } catch (\Exception $e) {
            AppLog::critical($e->getMessage());
            throw new ApiHttpException(503, "Service unavailable");
        }

        if($this->sessionId) {
            $this->start($this->sessionId);
        }

        return $this;
    }

    /**
     * @param $session_id
     * @return $this
     */
    public function start($session_id)
    {
        $this->sessionData = null;

        $rawData = $this->memCache->get($this->session_prefix . $session_id);

        $this->sessionId = $session_id;

        $this->sessionData = $this->_unserialize($rawData);

        return $this;
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

    /**
     * @return bool
     */
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
    private function _unserialize($session_data)
    {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
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
        $raw = '' ;
        $line = 0 ;
        $keys = array_keys( $data ) ;
        foreach( $keys as $key ) {
            $value = $data[ $key ] ;
            $line ++ ;

            $raw .= $key .'|' ;

            if( is_array( $value ) && isset( $value['huge_recursion_blocker_we_hope'] )) {
                $raw .= 'R:'. $value['huge_recursion_blocker_we_hope'] . ';' ;
            } else {
                $raw .= serialize( $value ) ;
            }
            $array[$key] = [ 'huge_recursion_blocker_we_hope' => $line ] ;
        }

        return $raw ;
    }
}