<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

/**
 * Class InputJson
 * @package App\Http\Middleware
 */
class LogHits
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     * @throws \App\Exceptions\Api\ApiHttpException
     * @throws \LogicException
     */
    public function handle($request, \Closure $next)
    {
        $this->capture($request);

        return $next($request);
    }

    private function capture(Request $request)
    {
        $route = $request->route();

        if (!$route)
        {
            return false;
        }

        $currentAction = $route->getActionName();

        if($currentAction) {
            app('Statsd')->registerHit($currentAction);
        }
    }
}
