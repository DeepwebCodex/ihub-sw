<?php

namespace App\Http\Middleware\BetGames;

/**
 * @package App\Http\Middleware
 */
class SetPartnerCashdesk
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        $uri = $request->route()->uri();
        if($uri === 'bg'){
            return $next($request);
        }

        $route = explode('/', $request->route()->uri());

        $service = $route[1] ?? 'favbet';

        $params = array_get(config('integrations.betGames.routes'), $service, 'favbet');

        $request->merge($params);

        return $next($request);
    }
}
