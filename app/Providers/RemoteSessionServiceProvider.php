<?php

namespace App\Providers;

use App\Components\ExternalServices\RemoteSession;
use Illuminate\Support\ServiceProvider;
use Testing\RemoteSessionMock;

class RemoteSessionServiceProvider extends ServiceProvider
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
        if($this->app->environment() == 'testing') {
            $this->app->singleton('RemoteSession', function ($app) {
                return RemoteSessionMock::getMock();
            });
        } else {
            $this->app->singleton('RemoteSession', function ($app) {
                return (new RemoteSession())->setUp();
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('RemoteSession');
    }
}
