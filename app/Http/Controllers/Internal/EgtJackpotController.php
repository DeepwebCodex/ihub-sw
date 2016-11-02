<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Components\Integrations\EuroGamesTech\Jackpot;

/**
 * Class EgtJackpotController
 * @package App\Http\Controllers\Internal
 */
class EgtJackpotController extends Controller
{
    /**
     * @var Jackpot
     */
    private $cache;

    public function __construct()
    {
        $this->cache = new Jackpot();
    }

    public function get()
    {
        return response($this->cache->get());
    }

    public function set()
    {
        $this->cache->set();
    }
}
