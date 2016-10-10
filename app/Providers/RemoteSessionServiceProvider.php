<?php

namespace App\Providers;

use App\Components\ExternalServices\RemoteSession;
use Illuminate\Support\ServiceProvider;
use Mockery;

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

                 $remote_session = Mockery::mock(RemoteSession::class);

                 $remote_session->shouldReceive('start')->andReturnSelf();
                 $remote_session->shouldReceive('get')->andReturn(1);
                 $remote_session->shouldReceive('getSessionId')->andReturn("KSKHDU95jG34");

                return $remote_session;
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
