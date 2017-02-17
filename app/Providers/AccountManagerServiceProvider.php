<?php

namespace App\Providers;

use App\Components\ExternalServices\AccountManager;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AccountManagerServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('AccountManager', function (Application $app) {
            return $app->make(AccountManager::class);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('AccountManager');
    }
}
