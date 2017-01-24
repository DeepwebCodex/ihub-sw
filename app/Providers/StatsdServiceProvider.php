<?php

namespace App\Providers;

use App\Components\ExternalServices\AmqpService;
use App\Components\ExternalServices\StatsdService;
use Illuminate\Support\ServiceProvider;

/**
 * Class AmqpServiceProvider
 * @package App\Providers
 */
class StatsdServiceProvider extends ServiceProvider
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
        $this->app->singleton('Statsd', function ($app) {
            return new StatsdService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Statsd'];
    }
}
