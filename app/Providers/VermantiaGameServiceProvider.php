<?php

namespace App\Providers;

use App\Components\ExternalServices\AccountManager;

use App\Components\ExternalServices\Vermantia\VermantiaGameService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class VermantiaGameServiceProvider extends ServiceProvider
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
     * @return AccountManager
     */
    public function register()
    {
        $this->app->singleton('VermantiaGameService', function (Application $app) {
            /** @var VermantiaGameService $accounting */
            $accounting = $app->make(VermantiaGameService::class);
            return $accounting;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('VermantiaGameService');
    }
}
