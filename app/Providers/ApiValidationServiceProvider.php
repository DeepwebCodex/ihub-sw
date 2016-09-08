<?php

namespace App\Providers;

use App\Components\ExternalServices\RemoteSession;
use App\Http\Requests\Validation\ApiValidator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationServiceProvider;

class ApiValidationServiceProvider extends ValidationServiceProvider
{

    /**
     * Register the validation factory.
     *
     * @return void
     */
    protected function registerValidationFactory()
    {
        $this->app->singleton('validator', function ($app) {
            $validator = new Factory($app['translator'], $app);

            // The validation presence verifier is responsible for determining the existence
            // of values in a given data collection, typically a relational database or
            // other persistent data stores. And it is used to check for uniqueness.
            if (isset($app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            $validator->resolver(function ($translator, array $data, array $rules, array $messages, array $customAttributes){
                return new ApiValidator($translator, $data, $rules, $messages, $customAttributes);
            });

            return $validator;
        });
    }
}
