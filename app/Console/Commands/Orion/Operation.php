<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\Request;
use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use App\Http\Requests\Validation\Orion\Validation;
use Exception;
use GuzzleHttp\Exception\RequestException;
use function app;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\Psr7\str;

/**
 * Description of Operation
 *
 * @author petroff
 */
trait Operation
{

    public function handleError(array $message, $level, string $module,
            string $line)
    {
        app('AppLog')->warning(json_encode($message), '', '', '', 'MicroGaming-Orion');
        $this->error('Something went wrong!');
    }

    public function handleSuccess(array $dataSuccess, array $elements = array())
    {
        $logRecords = [
            'data' => var_export($dataSuccess, true),
            'elements' => var_export($elements, true)
        ];
        app('AppLog')->info(json_encode($logRecords), '', '', '', 'MicroGaming-Orion');
        $this->info('Success.');
    }

    public function make(Request $requestQueueData,
            Validation $validatorQueueData, $operationsProcessor,
            Request $requestResolveData, Validation $validatorResolveData)
    {
        try {
            $bar = $this->output->createProgressBar(7);
            $bar->advance();
            $data = $requestQueueData->getData();
            $bar->advance();
            $validatorQueueData->validateBaseStructure($data);
            $bar->advance();
            $elements = $validatorQueueData->getData($data);
            $bar->advance();
            $handleCommitRes = $operationsProcessor->make($elements);
            $bar->advance();
            $dataResponse = $requestResolveData->getData($handleCommitRes);
            $bar->advance();
            $validatorResolveData->validateBaseStructure($dataResponse);
            $bar->advance();
            $bar->finish(); $this->info("\n");
            return $this->handleSuccess($dataResponse, $handleCommitRes);
        } catch (RequestException $re) {
            $logRecords = [
                'message' => str($re->getRequest())
            ];

            if ($re->hasResponse()) {
                $logRecords['data'] = str($re->getResponse());
            }
            $this->handleError($logRecords, 'warning', '', $re->getLine());
        } catch (CheckEmptyValidation $ve) {
            $this->handleSuccess(['message' => 'Source is empty']);
        } catch (Exception $ex) {
            $logRecords = [
                'message' => $ex->getMessage()
            ];
            $this->handleError($logRecords, 'errors', '', $ex->getLine());
        }
    }

}
