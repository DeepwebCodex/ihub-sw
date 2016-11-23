<?php

namespace App\Components;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Contracts\Foundation\Application;

class DetectEnvironment extends \Illuminate\Foundation\Bootstrap\DetectEnvironment
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
