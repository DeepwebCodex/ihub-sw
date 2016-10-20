<?php


use App\Components\Formatters\EgtXmlApiFormatter;
use App\Components\Formatters\JsonApiFormatter;
use App\Components\Formatters\MicroGamingApiFormatter;
use App\Components\Formatters\TextApiFormatter;
use App\Components\Formatters\XmlApiFormatter;
use App\Components\ThirdParty\Array2Xml;

class FormatterTest extends \Codeception\Test\Unit
{
    use Codeception\Specify;

    private $payload = [
        'data' => 'data',
        'second_test_data' => 'data2'
    ];

    protected function _before() {}

    protected function _after() {}

    // tests
    public function testJsonFormatter()
    {
        $formatter = new JsonApiFormatter();

        $exitPayload = array_merge($this->payload, ['message' => 'Test']);
        ksort($exitPayload);

        $this->specify("Test response formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatResponse(200, "Test", $this->payload);

            verify("Is response instance of Response", $response)->isInstanceOf(\Illuminate\Http\Response::class);

            verify("Is response of type JSON", $response->headers->contains('Content-type','application/json'))->true();

            verify("Is response status code equals 200", $response->getStatusCode())->equals(200);

            verify("Is response body is json", $response->getContent())
                ->equalsJsonString(json_encode($exitPayload));
        });

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatException(new \App\Exceptions\Api\ApiHttpException(400, "Test", $this->payload));

            verify("Is response instance of Response", $response)->isInstanceOf(\Illuminate\Http\Response::class);

            verify("Is response of type JSON", $response->headers->contains('Content-type','application/json'))->true();

            verify("Is response status code equals 400", $response->getStatusCode())->equals(400);

            verify("Is response body is json", $response->getContent())
                ->equalsJsonString(json_encode($exitPayload));
        });
    }

    // tests
    public function testXmlFormatter()
    {
        $formatter = new XmlApiFormatter();

        $exitPayload = array_merge($this->payload, ['message' => 'Test']);
        ksort($exitPayload);

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatResponse(200, "Test", $this->payload);

            verify("Is response instance of Response", $response)->isInstanceOf(\Illuminate\Http\Response::class);

            verify("Is response of type XML", $response->headers->contains('Content-type','application/xml'))->true();

            verify("Is response status code equals 200", $response->getStatusCode())->equals(200);

            verify("Is response body is xml", $response->getContent())
                ->equalsXmlString(Array2Xml::createXML('root', $exitPayload)->saveXML());
        });

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatException(new \App\Exceptions\Api\ApiHttpException(400, "Test", $this->payload));

            verify("Is response instance of Response", $response)->isInstanceOf(\Illuminate\Http\Response::class);

            verify("Is response of type XML", $response->headers->contains('Content-type','application/xml'))->true();

            verify("Is response status code equals 400", $response->getStatusCode())->equals(400);

            verify("Is response body is xml", $response->getContent())
                ->equalsXmlString(Array2Xml::createXML('root', $exitPayload)->saveXML());
        });
    }

    public function testEgtXmlFormatter()
    {
        $formatter = new EgtXmlApiFormatter();

        $exitPayload = array_merge($this->payload, ['message' => 'Test']);
        ksort($exitPayload);

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatResponse(200, "Test", $this->payload);

            verify("Is response body is json", $response->getContent())
                ->equalsXmlString($formatter->format($exitPayload));
        });

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatException(new \App\Exceptions\Api\ApiHttpException(400, "Test", $this->payload));

            verify("Is response body is xml", $response->getContent())
                ->equalsXmlString($formatter->format($exitPayload));
        });
    }

    public function testMicroGamingXmlFormatter()
    {
        $formatter = new MicroGamingApiFormatter();

        $exitPayload = array_merge($this->payload, ['message' => 'Test']);
        ksort($exitPayload);

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatResponse(200, "Test", $this->payload);

            verify("Is response body is xml", $response->getContent())
                ->equalsXmlString(Array2Xml::createXML('pkt', $exitPayload)->saveXML());
        });

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatException(new \App\Exceptions\Api\ApiHttpException(400, "Test", $this->payload));

            verify("Is response body is xml", $response->getContent())
                ->equalsXmlString(Array2Xml::createXML('pkt', $exitPayload)->saveXML());
        });
    }

    public function testTextXmlFormatter()
    {
        $formatter = new TextApiFormatter();

        $exitPayload = array_merge($this->payload, ['message' => 'Test']);

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatResponse(200, "Test", $this->payload);

            verify("Is exception response body is text", $response->getContent())
                ->equals(implode(' ', $exitPayload));
        });

        $this->specify("Test exception formatter", function() use($formatter, $exitPayload){
            /**@var \Illuminate\Http\Response $response*/
            $response = $formatter->formatException(new \App\Exceptions\Api\ApiHttpException(400, "Test", $this->payload));

            verify("Is response body is text", $response->getContent())
                ->equals(implode(' ', $exitPayload));
        });
    }
}