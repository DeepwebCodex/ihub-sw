<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/1/16
 * Time: 11:03 AM
 */

namespace App\Components\Traits;

use Illuminate\Support\Facades\Request;

trait MetaDataTrait
{
    private $metaStorageKey = '__response_meta';

    /**
     * @return \Illuminate\Http\Request
     */
    private function getRequest()
    {
        return Request::getFacadeRoot() ?: null;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function addMetaField(string $name, $value)
    {
        if ($request = $this->getRequest()) {
            $data = $request->input($this->metaStorageKey, []);
            $data = array_merge($data, [$name => $value]);
            $request->merge([$this->metaStorageKey => $data]);
        }
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setMetaData(array $data)
    {
        if ($request = $this->getRequest()) {
            $request->offsetSet($this->metaStorageKey, $data);
        }
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function pullMetaField(string $name)
    {
        $request = $this->getRequest();
        if ($request && $request->has($this->metaStorageKey)) {
            $data = $request->input($this->metaStorageKey);
            foreach ($data as $itemName => $value) {
                if ($itemName == $name) {
                    unset($data[$itemName]);
                    $request->offsetSet($this->metaStorageKey, $data);
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getMetaField(string $name)
    {
        $request = $this->getRequest();
        if ($request && $request->has($this->metaStorageKey)) {
            $data = $request->input($this->metaStorageKey);
            foreach ($data as $itemName => $value) {
                if ($itemName == $name) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * @return array|null|string
     */
    public function getMetaData()
    {
        $request = $this->getRequest();
        if ($request && $request->has($this->metaStorageKey)) {
            return $request->input($this->metaStorageKey);
        }
        return null;
    }
}
