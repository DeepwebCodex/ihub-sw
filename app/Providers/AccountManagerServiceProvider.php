<?php

namespace App\Providers;

use App\Components\ExternalServices\AccountManager;
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
        $this->app->singleton('AccountManager', function ($app) {
            return new AccountManager();
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
