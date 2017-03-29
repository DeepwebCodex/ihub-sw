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
use Symfony\Component\Console\Helper\ProgressBar;
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

    public function handleError(array $message, $level, string $module, string $line)
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

    public function make(Request $requestQueueData, Validation $validatorQueueData, $operationsProcessor,
            Request $requestResolveData, Validation $validatorResolveData)
    {
        $bar = new ProgressBar($this->output);
        try {
            $this->info('Geting data.\n');
            $data = $requestQueueData->getData();
            
            $this->info('Validating data.\n');
            $validatorQueueData->validateBaseStructure($data);
            $elements = $validatorQueueData->getData($data);
            
            $this->info('Processing data.\n');
            $bar->start(count($elements));
            $operationsProcessor->setBar($bar);
            $handleCommitRes = $operationsProcessor->make($elements);
            $bar->finish();
            $this->info("\n");
            
            $this->info('Sending data.\n');
            $dataResponse = $requestResolveData->getData($handleCommitRes);
            $validatorResolveData->validateBaseStructure($dataResponse);
            
            return $this->handleSuccess($dataResponse, $handleCommitRes);
        } catch (RequestException $re) {
            $bar->finish();
            $logRecords = [
                'message' => str($re->getRequest())
            ];

            if ($re->hasResponse()) {
                $logRecords['data'] = str($re->getResponse());
            }
            $this->handleError($logRecords, 'warning', '', $re->getLine());
        } catch (CheckEmptyValidation $ve) {
            $bar->finish();
            $this->handleSuccess(['message' => 'Source is empty']);
        } catch (Exception $ex) {
            $bar->finish();
            $logRecords = [
                'message' => $ex->getMessage()
            ];
            $this->handleError($logRecords, 'errors', '', $ex->getLine());
        }
    }

}
