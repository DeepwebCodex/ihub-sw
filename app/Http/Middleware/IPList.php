<?php


namespace App\Http\Middleware;

use App\Exceptions\Api\ApiHttpException;

class IPList
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $integration
     *
     * @return mixed
     */
    public function handle($request, \Closure $next, string $integration)
    {
        $ip = $request->ip();

        if($this->isValidIP($ip, $integration)){
            return $next($request);
        }

        throw new ApiHttpException(400, $ip .' '. trans('IP address is not permitted'));
    }

    private function isValidIP($ip, $integration)
    {
        $whitelist = config("integrations.{$integration}.allowed_ips");

        if (empty($whitelist) || in_array($ip, $whitelist)) {
            return true;
        }

        return false;
    }
}