<?php

namespace App\Providers;

use App\Components\Integrations\LiveDealer\ApiService;
use Illuminate\Support\ServiceProvider;

class LiveDealerApiServiceProvider extends ServiceProvider
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
        $this->app->singleton('LiveDealerApi', function ($app) {
            return new ApiService();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return ['LiveDealerApi'];
    }
}