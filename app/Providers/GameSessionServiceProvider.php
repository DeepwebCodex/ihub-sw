<?php

namespace App\Providers;

use App\Components\Integrations\GameSession\GameSessionService;
use Illuminate\Support\ServiceProvider;
use Testing\GameSessionsMock;

/**
 * Class GameSessionServiceProvider
 * @package App\Providers
 */
class GameSessionServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        if($this->app->environment() == 'testing') {
            $this->app->singleton('GameSession', function ($app) {
                return GameSessionsMock::getMock();
            });
        } else {
            $this->app->singleton('GameSession', function ($app) {
                return new GameSessionService();
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['GameSession'];
    }
}
