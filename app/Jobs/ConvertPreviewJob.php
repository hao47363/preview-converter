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
use Illuminate\Support\Facades\Http;

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

        $file_path = __DIR__ . '/../../../' . config('app.store_preview_video_folder') . '/preview/' . $this->vodId . '.mp4';

        if (file_exists($file_path)) {
            $apiEndpoint = config('app.get_vod_api_domain') . '/api/v1/vod/postPreview';
            try {
                $response = Http::post($apiEndpoint, [
                    'vodId' => $this->vodId,
                    'vodPreviewUrl' => config('app.preview_video_domain') . '/preview/' . $this->vodId . '.mp4',
                    'vodPreviewStatus' => 'Done',
                ]);
            } catch (\Exception $e) {
                throw $e;
            }
        } else {
            $apiEndpoint = config('app.get_vod_api_domain') . '/api/v1/vod/postPreview';
            try {
                $response = Http::post($apiEndpoint, [
                    'vodId' => $this->vodId,
                    'vodPreviewStatus' => 'Failed',
                ]);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $folderPath = public_path('preview/' . $this->vodId);

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }
    }
}
