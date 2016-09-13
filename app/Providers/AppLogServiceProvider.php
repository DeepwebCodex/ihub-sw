<?php

namespace App\Providers;

use App\Components\AppLog;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppLogServiceProvider
 * @package App\Providers
 */
class AppLogServiceProvider extends ServiceProvider
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
        $this->app->singleton('AppLog', function ($app) {
            return new AppLog();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['AppLog'];
    }
}
