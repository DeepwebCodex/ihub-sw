<?php
namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\MicroGamingHelper;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyCompleteGame;
use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyValidateBet;
use App\Components\Transactions\Strategies\MicroGaming\ProcessMicroGamingOrion;
use Exception;
use iHubGrid\Accounting\Users\IntegrationUser;
use iHubGrid\Accounting\Users\Interfaces\UserInterface;
use iHubGrid\ErrorHandler\Exceptions\Api\ApiHttpException;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHandler;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionHelper;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionRequest;
use iHubGrid\SeamlessWalletCore\Transactions\TransactionResponse;
use Illuminate\Support\Facades\Config;
use function app;
use function GuzzleHttp\json_decode;
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
        foreach ($data as $value) {
            $user_id = (int) $value['a:LoginName'];
            if ($value['a:RowId']) {
                $value['PreparedRowId'] = $value['a:RowId'];
                $requestName = ManuallyValidateBet::REQUEST_NAME;
            } else {
                $value['PreparedRowId'] = $value['a:RowIdLong'];
                $requestName = ManuallyCompleteGame::REQUEST_NAME;
            }
            try {
                $user = IntegrationUser::get($user_id, Config::get('integrations.microgaming.service_id'), 'microgaming');
                $response = $this->pushOperation($this->transType, $value, $user);
                $value['unlockType'] = $this->unlockType;
                $value['operationId'] = $response->operation_id;
                $value['isDuplicate'] = $response->isDuplicate;
                $dataRes[$requestName][] = $value;
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
                    $dataRes[$requestName][] = $value;
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
            Config::get('integrations.microgaming.service_id'), $data['a:TransactionNumber'], $user->id, $data['a:TransactionCurrency'], MicroGamingHelper::getTransactionDirection($typeOperation), TransactionHelper::amountCentsToWhole($data['a:ChangeAmount']), MicroGamingHelper::getTransactionType($typeOperation), $data['a:MgsReferenceNumber'], $data['a:GameName']
        );

        $transactionHandler = new TransactionHandler($transactionRequest, $user);
        $prossecor = new ProcessMicroGamingOrion($data);
        return $transactionHandler->handle($prossecor);
    }
}
