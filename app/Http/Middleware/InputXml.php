<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\ApiHttpException;
use Nathanmac\Utilities\Parser\Facades\Parser;

/**
 * Class InputJson
 * @package App\Http\Middleware
 */
class InputXml
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
            throw new ApiHttpException(400, trans('Empty source'));
        }

        try {
            $bodyContentDecoded = Parser::xml($bodyContent);

            $bodyContentDecoded = $this->collapseAttributes($bodyContentDecoded);
        } catch (\Exception $e) {
            throw new ApiHttpException(400, trans('Can\'t parse source'));
        }

        $request->merge($bodyContentDecoded);
        return $next($request);
    }

    /**
     * @param array $data
     * @return array|mixed
     */
    private function collapseAttributes(array $data)
    {
        if ($data) {
            foreach ($data as $name => $item) {
                if (is_array($item)) {
                    if ($name === '@attributes') {
                        $data = $this->collapseAttributes($item);
                    } else {
                        $data[$name] = $this->collapseAttributes($item);
                    }
                } else {
                    $data[$name] = $item;
                }
            }
        }

        return $data;
    }
}
