<?php

namespace App\Console\Commands;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;

/**
 * Class OptimizeLogIndices
 * @package App\Console\Commands
 */
class OptimizeLogIndices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize-log-indices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize Elasticsearch log indices';

    private $elasticUrl;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->elasticUrl = \config('external.elasticsearch.host');

        $this->info('Optimize log indices started');
        $this->createRequestResponseIndexTemplate();
        $this->info('Optimize log indices completed');
    }

    protected function createRequestResponseIndexTemplate()
    {
        $indexTemplatePattern = 'ihub-request-response-log-*';
        $indexTemplateJson = <<<JSON
        {
          "order": 99,
          "version": 7,
          "template": "{$indexTemplatePattern}",
          "mappings": {
            "_default_": {
              "properties": {
                "msg": {
                  "properties": {
                    "query": {
                      "type": "text",
                      "index": false,
                      "norms": false
                    },
                    "request": {
                      "type": "text",
                      "index": false,
                      "norms": false
                    },
                    "response": {
                      "type": "text",
                      "index": false,
                      "norms": false
                    },
                    "responseContentType": {
                      "type": "text",
                      "index": false,
                      "norms": false
                    },
                    "responseStatusCode": {
                      "type": "integer",
                      "index": false
                    }
                  }
                }
              }
            }
          }
        }
JSON;
        $indexTemplateName = 'ihub-request-response-log-template';
        $this->sendIndexTemplateCreateRequest($indexTemplateName, $indexTemplateJson);
    }

    /**
     * @param $indexTemplateName
     * @param $indexTemplateJson
     */
    protected function sendIndexTemplateCreateRequest($indexTemplateName, $indexTemplateJson)
    {
        $templateUri = '/_template/' . $indexTemplateName;
        $fullRequestUrl = $this->elasticUrl . $templateUri;

        /** @var Response $response */
        $response = \app('Guzzle')->request(
            'PUT',
            $fullRequestUrl,
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json'
                ],
                RequestOptions::BODY => $indexTemplateJson,
            ]
        );
        $responseBody = (string)$response->getBody();
        $responseData = \json_decode($responseBody, true);

        if ($responseData['acknowledged'] === true) {
            $this->info("Index template '{$indexTemplateName}' created");
            return;
        }

        $this->error('Critical error');
        $this->error($responseBody);
    }
}
