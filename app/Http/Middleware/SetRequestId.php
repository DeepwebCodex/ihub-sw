<?php

namespace App\Http\Middleware;

/**
 * Class SetRequestId
 * @package App\Http\Middleware
 */
class SetRequestId
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $request->headerRequestId = $request->hasHeader('X-Request-ID')
            ? $request->header('X-Request-ID') : gen_uid();

        return $next($request);
    }
}
