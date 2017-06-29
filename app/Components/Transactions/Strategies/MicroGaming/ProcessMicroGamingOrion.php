<?php
namespace App\Components\Transactions\Strategies\MicroGaming;

use App\Components\Integrations\MicroGaming\CodeMapping;
use App\Models\OrionTransaction;
use iHubGrid\SeamlessWalletCore\Transactions\Interfaces\TransactionProcessorInterface;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property  TransactionRequest $request
 * @property  CodeMapping $codeMapping;
 */
class ProcessMicroGamingOrion extends ProcessMicroGaming implements TransactionProcessorInterface
{

    private $additionalData;

    public function __construct(array $data)
    {
        $this->additionalData = $data;
    }

    protected function getBetRecords()
    {
        $betTransaction = parent::getBetRecords();
        if ($betTransaction) {
            $this->request->partner_id = $betTransaction->partner_id;
            $this->request->cashdesk_id = $betTransaction->cashdesk;
        }
        return $betTransaction;
    }

    protected function writeTransaction($model = null, string $newStatus = null)
    {
        return DB::transaction(function () use($model, $newStatus) {
                $model = parent::writeTransaction($model, $newStatus);
                if ($model instanceof Model) {
                    OrionTransaction::firstOrCreate([
                        'transaction_id' => $model->id,
                        'row_id' => $this->additionalData['a:RowId'],
                        'row_id_long' => $this->additionalData['a:RowIdLong'],
                    ]);
                }
                return $model;
            });
    }
}
