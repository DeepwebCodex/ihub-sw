<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 8/31/16
 * Time: 10:38 AM
 */

namespace App\Components\Formatters;

use App\Components\Traits\MetaDataTrait;
use App\Exceptions\Api\ApiHttpException;
use App\Exceptions\Api\Templates\IExceptionTemplate;
use Illuminate\Http\Response;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * @param  IExceptionTemplate $exceptionTemplate exception template class
*/
abstract class BaseApiFormatter
{
    use MetaDataTrait;

    private $exceptionTemplate;

    /**
     * @param array $data
     * @return string
     */
    abstract public function format(array $data);

    /**
     * @param \Exception $exception
     * @return Response
     */
    abstract public function formatException(\Exception $exception);

    /**
     * @param $statusCode
     * @param $message
     * @param $payload
     * @return Response
     */
    abstract public function formatResponse($statusCode, string $message, array $payload = []);

    public function setTemplate($templateClass)
    {
        if ($templateClass) {
            $obj = new $templateClass();
            if ($obj instanceof IExceptionTemplate) {
                $this->exceptionTemplate = $obj;
            } else {
                throw new \InvalidArgumentException();
            }
        }
    }

    /**
     * @param array $payload
     * @param int $statusCode
     * @param bool $isApiException
     * @return array|mixed
     */
    private function mapPayload(array $payload, int $statusCode, bool $isApiException)
    {
        if ($this->exceptionTemplate && $this->exceptionTemplate instanceof IExceptionTemplate) {
            $result = array_map([$this->exceptionTemplate, 'mapping'], [$payload], [$statusCode], [$isApiException]);

            return reset($result);
        }

        return $payload;
    }

    /**
     * @param \Exception $exception
     * @return array
     */
    protected function transformException(\Exception $exception)
    {
        $e = FlattenException::create($exception);

        $exceptionData = [
            'payload' => [],
            'statusCode' => 500
        ];

        if ($exceptionMessage = $e->getMessage()) {
            if ($exceptionDecodedMessage = json_decode($exceptionMessage, true)) {
                $exceptionData['payload'] = $exceptionDecodedMessage;
            } else {
                $exceptionData['payload']['message'] = $exceptionMessage;
            }
        }

        if ($e->getCode()) {
            $exceptionData['payload'] = array_merge($exceptionData['payload'], ['code' => $e->getCode()]);
        }

        $metaData = $this->getMetaData();
        if ($metaData) {
            $exceptionData['payload'] = array_merge($exceptionData['payload'], $metaData);
        }

        $exceptionData['statusCode'] = $e->getStatusCode();

        $exceptionData['isApiException'] = config('app.debug') ? true : $exception instanceof ApiHttpException;

        $exceptionData['payload'] = $this->mapPayload(
            $exceptionData['payload'],
            $exceptionData['statusCode'],
            $exceptionData['isApiException']
        );

        return $exceptionData;
    }
}
