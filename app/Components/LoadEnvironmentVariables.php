<?php

namespace App\Components;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Contracts\Foundation\Application;

class LoadEnvironmentVariables extends \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (! $app->configurationIsCached()) {
            $this->checkForSpecificEnvironmentFile($app);

            try {
                (new Dotenv($app->environmentPath(), $app->environmentFile()))->overload();
            } catch (InvalidPathException $e) {
                //
            }
        }
    }
}
