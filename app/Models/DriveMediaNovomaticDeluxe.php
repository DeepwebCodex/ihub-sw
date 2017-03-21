<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriveMediaNovomaticDeluxe extends Model {

    /**
     * {@inheritdoc}
     */
    protected $connection = 'integration'; //TODO::fill

    /**
     * {@inheritdoc}
     */
    protected $table = 'drivemedia_novomatic_deluxe';

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;
    protected $fillable = [
        'betInfo',
        'bet',
        'winLose',
        'matrix',
        'packet',
        'parent_id'
    ];

}
