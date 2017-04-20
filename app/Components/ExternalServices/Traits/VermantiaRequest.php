<?php

namespace App\Components\ExternalServices\Traits;

/**
 * Request handling for account ROH post API
 */

use iHubGrid\ErrorHandler\Exceptions\Api\GenericApiHttpException;
use iHubGrid\ErrorHandler\Facades\AppLog;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Response;
use Nathanmac\Utilities\Parser\Facades\Parser;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Stringy\StaticStringy as S;

trait VermantiaRequest
{
    protected function sendGet(string $url, array $params, int $retry = 0){

        $params = array_filter($params);

        try {
            $response = app('Guzzle')->request(
                'GET',
                $url,
                [
                    RequestOptions::QUERY => $params
                ]
            );

            if ($response->getStatusCode() >= Response::HTTP_OK && $response->getStatusCode() < Response::HTTP_NOT_EXTENDED) {
                if ($data = $response->getBody()) {
                    try {

                        $bodyContentDecoded = Parser::xml($data);

                        $bodyContentDecoded = $this->collapseAttributes($bodyContentDecoded);

                    } catch (\Exception $e) {
                        throw new GenericApiHttpException(400, trans('Can\'t parse source'));
                    }

                    if(isset($bodyContentDecoded['Code']) && isset($bodyContentDecoded['Reason'])) {
                        throw new \Exception("VermantiaGameService " . $bodyContentDecoded['Code'] . " : " . $bodyContentDecoded['Reason']);
                    }

                    return $bodyContentDecoded;
                }
                throw new BadRequestHttpException();
            }

        } catch (\Exception $e) {

            if ($retry > 0) {
                $retry--;
                sleep(1);
                return $this->sendGet($url, $params, $retry);
            }

            AppLog::critical([
                'message' => $e->getMessage(),
                'params'  => $params
            ]);

            throw new GenericApiHttpException(500, $e->getMessage());
        }
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