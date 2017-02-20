<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Console\Commands\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\Request;
use App\Exceptions\Internal\Orion\CheckEmptyValidation;
use App\Facades\AppLog;
use App\Http\Requests\Validation\Orion\Validation;
use Exception;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Psr7\str;

/**
 * Description of Operation
 *
 * @author petroff
 */
trait Operation {

    public function handleError(string $message, $level, string $module, string $line) {
        AppLog::warning($message, 'ORION', $module, $line);
        $this->error('Something went wrong!');
    }

    public function handleSuccess(array $dataSuccess, array $elements = array()) {
        AppLog::info('Success. Data: ' . print_r($dataSuccess, true) . " Data processed: " . print_r($elements, true), 'ORION', __CLASS__, __LINE__);
        $this->info('Success.');
    }

    public function make(Request $requestQueueData, Validation $validatorQueueData, $operationsProcessor, Request $requestResolveData, Validation $validatorResolveData) {
        AppLog::info('START ', 'ORION', __CLASS__, __LINE__);
        $request = \Illuminate\Support\Facades\Request::getFacadeRoot();
        $request->server->set('PARTNER_ID', 1);
        $request->server->set('FRONTEND_NUM', -5);
        try {
            $data = $requestQueueData->getData();
            $validatorQueueData->validateBaseStructure($data);
            $elements = $validatorQueueData->getData($data);
            $handleCommitRes = $operationsProcessor->make($elements);
            $dataResponse = $requestResolveData->getData($handleCommitRes);
            $validatorResolveData->validateBaseStructure($dataResponse);
            return $this->handleSuccess($dataResponse, $handleCommitRes);
        } catch (RequestException $re) {
            $message = 'Request has error.  Request: ' . str($re->getRequest());
            if ($re->hasResponse()) {
                $message .= " Response" . str($re->getResponse());
            }
            $this->handleError($message, 'warning', '', $re->getLine());
        } catch (CheckEmptyValidation $ve) {
            $this->handleSuccess(['message' => 'Source is empty']);
        } catch (Exception $ex) {
            $this->handleError($ex->getMessage(), 'errors', '', $ex->getLine());
        }
    }

}
