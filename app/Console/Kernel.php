<?php

namespace App\Console;

use App\Components\LoadEnvironmentVariables;
use App\Console\Commands\CancelPendingOperations;
use App\Console\Commands\MaksymenkoTest;
use App\Console\Commands\OptimizeLogIndices;
use App\Console\Commands\TransactionHistoryStatusUpdate;
use iHubGrid\MicroGaming\Commands\MicrogamingSequence;
use iHubGrid\MicroGaming\Commands\Orion\Commit;
use iHubGrid\MicroGaming\Commands\Orion\EndGame;
use iHubGrid\MicroGaming\Commands\Orion\Rollback;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $bootstrappers = [
        LoadEnvironmentVariables::class,
        'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        'Illuminate\Foundation\Bootstrap\HandleExceptions',
        'Illuminate\Foundation\Bootstrap\RegisterFacades',
        'Illuminate\Foundation\Bootstrap\SetRequestForConsole',
        'Illuminate\Foundation\Bootstrap\RegisterProviders',
        'Illuminate\Foundation\Bootstrap\BootProviders',
    ];

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //FeedListener::class
        Commit::class,
        Rollback::class,
        EndGame::class,
        MicrogamingSequence::class,
        CancelPendingOperations::class,
        MaksymenkoTest::class,
        OptimizeLogIndices::class,
        TransactionHistoryStatusUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        //require base_path('routes/console.php');
    }
}
