<?php

namespace App\Console\Commands;

use App\Jobs\ConvertPreviewJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertPreview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'preview:convert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert preview video';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $macVodData = DB::select('SELECT * FROM mac_vod where vod_down_url = ""');

        foreach ($macVodData as $macVod) {
            $macVodDownloadLink = $this->extractString($macVod->vod_play_url);
            if (!empty($macVodDownloadLink)) {
                $videoFileName = basename($macVodDownloadLink);
                $vodIdDirectory = public_path('preview/' . $macVod->vod_id);
                $savePath = $vodIdDirectory . '/' . $videoFileName;

                if (!file_exists($vodIdDirectory)) {
                    mkdir($vodIdDirectory, 0777, true);
                }

                if (file_exists($vodIdDirectory)) {
                    file_put_contents($savePath, fopen($macVodDownloadLink, 'r'));

                    if (file_exists($savePath)) {
                        $fileContent = file_get_contents($savePath);
                        $lines = explode("\n", $fileContent);

                        if (isset($lines[2])) {
                            $parsedUrl = parse_url($macVodDownloadLink);
                            if ($parsedUrl !== false && isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                                $newMacVodDownloadLink = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $lines[2];
                            }
                        }

                        if (!empty($newMacVodDownloadLink)) {
                            $newVodIdDirectory = __DIR__ . '/../../../../' . config('app.target_domain') . '/preview/' . $macVod->vod_id;

                            if (!file_exists($newVodIdDirectory . '/preview')) {
                                mkdir($newVodIdDirectory . '/preview', 0777, true);
                            }

                            $command = './random.sh ' . $newVodIdDirectory . ' ' . $newMacVodDownloadLink;

                            DB::table('mac_vod')
                                ->where('vod_id', $macVod->vod_id)
                                ->update(
                                    [
                                        'vod_down_url' => 'https://asd.uw1wieda.com/preview/' . $macVod->vod_id . '.mp4',
                                        'vod_down_note' => 'Converting'
                                    ],
                                );

                            dispatch(new ConvertPreviewJob($command, $macVod->vod_id));
                        }
                    }
                }
            }
        }
    }

    function extractString($inputString)
    {
        $regex = '/https:\/\/.*?\.m3u8/';
        if (preg_match($regex, $inputString, $matches)) {
            return $matches[0];
        }
        return '';
    }
}
