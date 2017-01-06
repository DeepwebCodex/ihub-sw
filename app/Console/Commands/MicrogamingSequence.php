<?php

namespace App\Console\Commands;

use App\Models\MicroGamingProdObjectIdMap;
use Illuminate\Console\Command;

class MicrogamingSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microgaming:sequence {value : The max microgaming object id from accounting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets object id map sequence for MicroGaming';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mapModel = new MicroGamingProdObjectIdMap();

        $connection = $mapModel->getConnectionName();

        $table = $mapModel->getTable();

        $sequence_name = $table . '_id_seq';

        \DB::connection($connection)->statement("ALTER SEQUENCE {$sequence_name} RESTART WITH {$this->argument('value')};");

        $sequnce_value = (array) \DB::connection($connection)->select("SELECT last_value FROM {$sequence_name}");

        $sequnce_value = reset($sequnce_value);

        return $this->info("Current sequence value is: " . $sequnce_value->last_value);
    }
}
