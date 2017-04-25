<?php

namespace App\Http\Middleware;

use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;

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
     * @throws ApiHttpException
     * @throws \LogicException
     */
    public function handle($request, \Closure $next)
    {
        if ($pId = $request->input('partner_id')) {
            $request->server->set('PARTNER_ID', (int)$pId);
            return $next($request);
        }

        if (is_numeric($request->server('PARTNER_ID'))) {
            return $next($request);
        }

        /** @var \App\Components\AppLog $logger */
        $logger = app('AppLog');
        $logger->critical('PARTNER_ID not found');

        throw new ApiHttpException(503, 'Service unavailable');
    }
}
