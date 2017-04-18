<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MaksymenkoTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maksymenko:test {user_id} {direction} {object_id} {service_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $userId = (int)$this->argument('user_id');
        $direction = (int)$this->argument('direction');
        $objectId = (int)$this->argument('object_id');
        $serviceId = (int)$this->argument('service_id');

        $operations = app('AccountManager')->getOperations($userId, $direction, $objectId, $serviceId);

        dd($operations);
    }
}
