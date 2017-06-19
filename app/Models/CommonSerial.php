<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommonSerial extends Model {

    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = '';

    /**
     * {@inheritdoc}
     */
    public function getSerial() {
        $mapModel = new static();
        $connection = $mapModel->getConnectionName();
        $value = DB::connection($connection)->select("SELECT nextval('common_integration_serial')");
        return intval($value['0']->nextval);
    }

}
