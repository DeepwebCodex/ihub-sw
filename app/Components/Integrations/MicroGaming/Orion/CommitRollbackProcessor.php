<?php

namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGamingOrion;
use App\Components\Transactions\TransactionHandler;
use App\Components\Transactions\TransactionHelper;
use App\Components\Transactions\TransactionRequest;
use App\Components\Transactions\TransactionResponse;
use App\Components\Users\IntegrationUser;
use App\Components\Users\Interfaces\UserInterface;
use App\Exceptions\Api\ApiHttpException;
use Exception;
use Illuminate\Support\Facades\Config;
use function app;
use function dd;
use function GuzzleHttp\json_encode;

class CommitRollbackProcessor implements IOperationsProcessor
{

    //RollbackQueue

    protected $unlockType;
    protected $transType;
    protected $bar;

    function __construct(string $unlockType, string $transType)
    {
        $this->unlockType = $unlockType;
        $this->transType = $transType;
    }

    function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function make(array $data): array
    {
        $dataRes = array();
        foreach ($data as $key => $value) {
            $user_id = (int) $value['a:LoginName'];
            try {
                $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
                $response = $this->pushOperation($this->transType, $value, $user);
                $value['unlockType'] = $this->unlockType;
                $value['operationId'] = $response->operation_id;
                $value['isDuplicate'] = $response->isDuplicate;
                $dataRes[] = $value;
                if ($this->bar) {
                    $this->bar->advance();
                }
            } catch (ApiHttpException $e) {
                $rawStr = $e->getMessage();
                $payload = json_decode($rawStr, true);
                if (isset($payload['message']) && $payload['message'] == 'Invalid operation order') {
                    $value['unlockType'] = $this->unlockType;
                    $value['operationId'] = app('AccountManager')->getFreeOperationId();
                    $value['isDuplicate'] = false;
                    $dataRes[] = $value;
                    $group = 'MicroGaming-Orion-BadOrderTransaction';
                } else {
                    $group = 'MicroGaming-Orion';
                }

                $logRecords = [
                    'data' => var_export($value, true),
                    'message' => var_export($e->getMessage(), true)
                ];
                app('AppLog')->warning(json_encode($logRecords), '', '', '', $group);
            } catch (Exception $e) {
                $logRecords = [
                    'data' => var_export($value, true),
                    'message' => var_export($e->getMessage(), true)
                ];
                app('AppLog')->warning(json_encode($logRecords), '', '', '', 'MicroGaming-Orion');
            }
        }

        return $dataRes;
    }

    public function pushOperation(string $typeOperation, array $data, UserInterface $user): TransactionResponse
    {

        $transactionRequest = new TransactionRequest(
                Config::get('integrations.microgaming.service_id'), $data['a:TransactionNumber'], $user->id, $user->getCurrency(), MicroGamingHelper::getTransactionDirection($typeOperation), TransactionHelper::amountCentsToWhole($data['a:ChangeAmount']), MicroGamingHelper::getTransactionType($typeOperation), $data['a:MgsReferenceNumber'], $data['a:GameName']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);

        return $transactionHandler->handle(new ProcessMicroGamingOrion());
    }

}
