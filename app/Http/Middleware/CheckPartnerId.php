<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\ApiHttpException;

/**
 * Class InputJson
 * @package App\Http\Middleware
 */
class CheckPartnerId
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
        $partnerId = (int) $request->server('PARTNER_ID');

        if ($partnerId && is_numeric($partnerId)) {
            return $next($request);
        }

        /** @var \App\Components\AppLog $logger */
        $logger = app('AppLog');
        $logger->critical('PartnerId not found');

        throw new ApiHttpException(503, 'Service unavailable');
    }
}
