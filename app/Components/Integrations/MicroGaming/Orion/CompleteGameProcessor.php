<?php
namespace App\Components\Integrations\MicroGaming\Orion;

use App\Components\Integrations\MicroGaming\Orion\Request\ManuallyCompleteGame;

class CompleteGameProcessor implements IOperationsProcessor
{

    protected $bar;

    function setBar($bar)
    {
        $this->bar = $bar;
    }

    public function make(array $data): array
    {
        $dataReturn = array();
        foreach ($data as $value) {
            if ($this->bar) {
                $this->bar->advance();
            }
            $value['PreparedRowId'] = $value['a:RowId'];
            $dataReturn[ManuallyCompleteGame::REQUEST_NAME][] = $value;
        }
        return $dataReturn;
    }
}
