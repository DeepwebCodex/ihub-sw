<?php

namespace App\Providers;

use App\Components\Integrations\GameSession\GameSessionService;
use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton('GameSession', function ($app) {
            return new GameSessionService();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['GameSession'];
    }
}
