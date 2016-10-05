<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 10/5/16
 * Time: 2:55 PM
 */

namespace Codeception\Module;

use App\Components\Codeception\Module\LaravelExtendedConnector;

class Laravel5Extended extends Laravel5
{
    /**
     * Before hook.
     *
     * @param \Codeception\TestInterface $test
     */
    public function _before(\Codeception\TestInterface $test)
    {
        $this->client = new LaravelExtendedConnector($this);

        if ($this->config['run_database_migrations']) {
            // Must be called before database transactions are started
            $this->callArtisan('migrate');
        }

        if (isset($this->app['db']) && $this->config['cleanup']) {
            $this->app['db']->beginTransaction();
        }
    }
}