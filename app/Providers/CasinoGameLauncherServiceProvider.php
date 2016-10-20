<?php

namespace App\Providers;

use App\Components\Integrations\CasinoGameLauncher;
use Illuminate\Support\ServiceProvider;

/**
 * Class CasinoGameLauncherServiceProvider
 * @package App\Providers
 */
class CasinoGameLauncherServiceProvider extends ServiceProvider
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
        $this->app->singleton('CasinoGameLauncher', function ($app) {
            return new CasinoGameLauncher();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['CasinoGameLauncher'];
    }
}
