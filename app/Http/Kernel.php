<?php

namespace App\Http;

use App\Components\LoadEnvironmentVariables;
use iHubGrid\ErrorHandler\Http\Middleware\InputJson;
use iHubGrid\ErrorHandler\Http\Middleware\InputXml;
use iHubGrid\ErrorHandler\Http\Middleware\LogRequestResponse;
use iHubGrid\Fundist\Http\Middleware\ParsePlayerIdOnOffline;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \iHubGrid\ErrorHandler\Http\Middleware\SetRequestId::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'check.partner_id',
        ],

        'api' => [
            'bindings',
            'check.partner_id',
            'log.request.response',
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'input.json' => InputJson::class,
        'check.partner_id' => \App\Http\Middleware\CheckPartnerId::class,
        'input.xml' => InputXml::class,
        'input.dm.parselogin' => \App\Http\Middleware\DriveMedia\ParseLogin::class,
        'log.request.response' => LogRequestResponse::class,
        'check.ip' => \App\Http\Middleware\IPList::class,
        'input.bg.parsePlayerIdOnWin' => \iHubGrid\BetGames\Http\Middleware\ParsePlayerIdOnWin::class,
        'input.bg.setPartnerCashdesk' => \iHubGrid\BetGames\Http\Middleware\SetPartnerCashdesk::class,
        'input.fundist.parsePlayerIdOnOffline' => ParsePlayerIdOnOffline::class,
    ];
}
