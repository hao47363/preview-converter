<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $command;

    /**
     * Create a new job instance.
     */
    public function __construct($command)
    {
        $this->command = $command;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        exec($this->command, $output, $returnValue);
    }
}
