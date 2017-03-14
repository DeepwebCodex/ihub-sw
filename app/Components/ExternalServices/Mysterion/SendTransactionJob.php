<?php

namespace App\Components\ExternalServices\Mysterion;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTransactionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     *
     * @param $message
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @param SendService $sendService
     * @return void
     * @throws \Exception
     */
    public function handle(SendService $sendService)
    {
        try {
            $sendService->sendData($this->message);
        } catch (\Exception $exception) {
            app('AppLog')->error($exception->getMessage(), 'mysterion_transactions');
            throw $exception;
        }
    }
}
