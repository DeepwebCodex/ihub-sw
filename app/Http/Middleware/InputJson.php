<?php

namespace App\Http\Middleware;
use App\Exceptions\Api\ApiHttpException;

/**
 * Class InputJson
 * @package App\Http\Middleware
 */
class InputJson
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
        $bodyContent = $request->getContent();
        if (!$bodyContent) {
            throw new ApiHttpException(500, trans('api/all.s_empty_source'));
        }

        $bodyContentDecoded = json_decode($bodyContent, true);
        if ($bodyContentDecoded && json_last_error() === JSON_ERROR_NONE) {
            $request->merge($bodyContentDecoded);
            return $next($request);
        }

        throw new ApiHttpException(500, trans('api/all.s_cant_parse_source'));
    }
}
