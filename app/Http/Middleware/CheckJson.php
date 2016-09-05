<?php

namespace App\Http\Middleware;

/**
 * Class CheckJson
 * @package App\Http\Middleware
 */
class CheckJson
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     * @throws \LogicException
     * @throws \DomainException
     */
    public function handle($request, \Closure $next)
    {
        $bodyContent = $request->getContent();
        if ($bodyContent
            && json_decode($bodyContent)
            && json_last_error() === JSON_ERROR_NONE
        ) {
            return $next($request);
        }
        throw new \DomainException('JSON format required');
    }
}
