<?php

namespace App\Http\Middleware;

use App\Exceptions\Api\ApiHttpException;
use Nathanmac\Utilities\Parser\Facades\Parser;
use Stringy\StaticStringy as S;

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
        if (app()->environment() == 'testing') {
            return $next($request);
        }

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
     * @param string $parentName
     * @return array|mixed
     */
    private function collapseAttributes(array $data, $parentName = '')
    {
        if ($data) {
            foreach ($data as $name => $item) {
                if ($parentName && $name === '#text') {
                    $data[$parentName] = $item;
                } else {

                    if(S::startsWith($name, '@') && isset($data[$name])) {
                        unset($data[$name]);
                    }

                    $name = ltrim($name, '@');
                    if (is_array($item)) {
                        $data[$name] = $this->collapseAttributes($item, $name);
                    } else {
                        $data[$name] = $item;
                    }
                }
            }
        }
        return $data;
    }
}
