<?php

namespace App\Providers;

use App\Log\ExternalRequestLogger;
use App\Log\ExternalRequestMessageFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\ServiceProvider;

/**
 * Class GuzzleServiceProvider
 * @package App\Providers
 */
class GuzzleServiceProvider extends ServiceProvider
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
        $this->app->bind('Guzzle', function () {
            return new Client($this->getGuzzleClientOptions());
        });
    }

    /**
     * @return array
     */
    protected function getGuzzleClientOptions(): array
    {
        $defaultClientOptions = [];

        if (!\config('log.external-services-requests.is_enabled')) {
            return $defaultClientOptions;
        }
        $callerClass = $this->getExternalServiceClass();
        if (!$callerClass) {
            return $defaultClientOptions;
        }
        if ($this->isExcludedLogModule($callerClass)) {
            return $defaultClientOptions;
        }
        $handler = HandlerStack::create();
        $logger = new ExternalRequestLogger($callerClass);
        $messageFormatter = new ExternalRequestMessageFormatter();
        $middleware = Middleware::log($logger, $messageFormatter);
        $handler->push($middleware);

        return ['handler' => $handler];
    }

    /**
     * @return string|null
     */
    protected function getExternalServiceClass()
    {
        $stackTrace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 25);
        $stackItem = \array_first($stackTrace, function ($value, $key) {
            if (!isset($value['class'])) {
                return false;
            }
            return \strpos($value['class'], 'ExternalServices') !== false;
        });
        if (!$stackItem) {
            return null;
        }
        return $stackItem['class'];
    }

    /**
     * @param string $callerClass
     * @return bool
     */
    protected function isExcludedLogModule(string $callerClass): bool
    {
        $excludeFilterModules = (array)\config('log.external-services-requests.exclude_filter');
        $moduleExcluded = \array_first($excludeFilterModules, function ($value, $key) use ($callerClass) {
            return \strpos($callerClass, $value) !== false;
        });
        return (bool)$moduleExcluded;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['Guzzle'];
    }
}
