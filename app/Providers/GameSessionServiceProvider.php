<?php

namespace App\Providers;

use App\Components\Integrations\GameSession\GameSessionService;
use Illuminate\Contracts\Foundation\Application;
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
        $this->app->singleton('GameSession', function (Application $app) {
            return $app->make(GameSessionService::class);
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
