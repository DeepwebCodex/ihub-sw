<?php

namespace App\Models\Erlybet;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseErlybetModel
 * @package App\Models\Erlybet
 */
class BaseErlybetModel extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $connection = 'erlybet';
}
