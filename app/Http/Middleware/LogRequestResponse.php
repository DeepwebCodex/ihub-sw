<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\Traits\ApiHandlerTrait;

/**
 * Class InputJson
 * @package App\Http\Middleware
 */
class LogRequestResponse
{
    use ApiHandlerTrait;
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
        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function terminate($request, $response)
    {
        if (env('APP_REQUEST_DEBUG', false) && $controller = $this->isApiCall($request)) {
            app('AppLog')->info([
                'request' => $request->getContent(),
                'response' => $response->getContent(),
                'query' => $request->getUri()
            ], $this->getNodeName(), 'request-response-log');
        }
    }
}
