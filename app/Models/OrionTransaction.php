<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrionTransaction extends Model
{

    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'orion_resolver';
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'transaction_id',
        'row_id',
        'row_id_long'
    ];

}
