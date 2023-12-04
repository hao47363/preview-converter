<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ConvertPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $command;
    protected $vodId;

    /**
     * Create a new job instance.
     */
    public function __construct($command, $vodId)
    {
        $this->command = $command;
        $this->vodId = $vodId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        exec($this->command, $output, $returnValue);

        $file_path = __DIR__ . '/../../../h5-uat.com/preview/' . $this->vodId . '.mp4';

        if (file_exists($file_path)) {
            DB::table('mac_vod')
                ->where('vod_id', $this->vodId)
                ->update(
                    ['vod_down_note' => 'Done']
                );
        } else {
            DB::table('mac_vod')
                ->where('vod_id', $this->vodId)
                ->update(
                    [
                        'vod_down_url' => '',
                        'vod_down_note' => 'Failed'
                    ]
                );

            // DB::table('mac_vod')
            //     ->where('vod_id', $this->vodId)
            //     ->update(
            //         [
            //             'vod_down_url' => 'https://asd.uw1wieda.com/preview/' . $this->vodId . '.mp4',
            //             'vod_down_note' => 'Converting'
            //         ],
            //     );

            // dispatch(new ConvertPreviewJob($this->command, $this->vodId));
        }

        $folderPath = public_path('preview/' . $this->vodId);

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }
    }
}
