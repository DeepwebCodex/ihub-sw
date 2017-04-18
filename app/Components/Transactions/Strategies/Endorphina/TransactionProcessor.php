<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Components\Transactions\Strategies\Endorphina;

use App\Components\Integrations\NetEntertainment\CodeMapping;
use App\Components\Integrations\NetEntertainment\StatusCode;
use App\Components\Transactions\BaseSeamlessWalletProcessor;
use App\Components\Transactions\Interfaces\TransactionProcessorInterface;
use App\Components\Transactions\TransactionRequest;
use App\Exceptions\Api\ApiHttpException;
use App\Models\DriveMediaNovomaticDeluxe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\json_encode;

/**
 * Description of Transaction
 *
 * @author petroff
 */
abstract class TransactionProcessor extends BaseSeamlessWalletProcessor implements TransactionProcessorInterface {

    protected $additional;

    function __construct(Request $request) {
        $this->additional = $request;
    }

    protected function make(Model $lastRecord = null) {

        $status = is_object($lastRecord) ? $lastRecord->status : null;

        switch ($status) {
            case TransactionRequest::STATUS_NULL:
                if ($newRecord = $this->runPending()) {
                    $this->runCompleted($this->responseData['operation_id'], $newRecord);
                }
                break;
            case TransactionRequest::STATUS_PENDING:
                $this->runCompleted($lastRecord->operation_id, $lastRecord);
                break;
            case TransactionRequest::STATUS_COMPLETED:
                $this->responseData = $lastRecord->attributesToArray();
                $this->isDuplicate = true;
                break;
            case TransactionRequest::STATUS_CANCELED:
                $this->responseData = null;
                $this->isDuplicate = true;
                break;
            default:
                break;
        }

        return $this->responseData;
    }

    protected function writeTransaction($model = null, string $newStatus = null) {
        if ($model && $newStatus && $newStatus != $model->status) {
            return parent::writeTransaction($model, $newStatus);
        } else {
            DB::beginTransaction();
            $model = parent::writeTransaction($model, $newStatus);
            if (!$model) {
                DB::rollBack();
                return $model;
            }
            $res = DriveMediaNovomaticDeluxe::create([
                        'betInfo' => $this->additional->input('betInfo'),
                        'bet' => $this->additional->input('bet'),
                        'winLose' => $this->additional->input('winLose'),
                        'matrix' => $this->additional->input('matrix'),
                        'packet' => json_encode($this->additional->all()),
                        'parent_id' => $model->id
            ]);
            if (!$res) {
                DB::rollBack();
                return $res;
            }
            DB::commit();
        }

        return $model;
    }

    protected function onInsufficientFunds($e) {
        throw new ApiHttpException($e->getStatusCode(), null, CodeMapping::getByErrorCode
                (StatusCode::FAIL_BALANCE));
    }

}
