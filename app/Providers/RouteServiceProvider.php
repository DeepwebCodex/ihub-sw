<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace . '\Api'
        ], function () {
            $path = base_path('routes/api');
            $regexIterator = $this->getRegexIteratorForPath($path, '/^.+\.php$/i');
            $routeBasePath = $path;

            foreach ($regexIterator as $route) {
                $routeFilePath = $route[0];
                $routePath = str_replace($routeBasePath, '', $routeFilePath);
                Route::group(
                    ['prefix' => pathinfo($routePath, PATHINFO_DIRNAME)],
                    function () use ($routeFilePath) {
                        require $routeFilePath;
                    }
                );
            }
        });
    }

    /**
     * @param $path
     * @param $regexRule
     * @return \RegexIterator
     */
    protected function getRegexIteratorForPath(string $path, string $regexRule): \RegexIterator
    {
        $directoryIterator = new \RecursiveDirectoryIterator($path);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        return new \RegexIterator($iterator, $regexRule, \RecursiveRegexIterator::GET_MATCH);
    }
}
