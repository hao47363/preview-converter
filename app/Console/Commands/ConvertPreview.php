<?php

namespace App\Console\Commands;

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
        $macVodData = DB::select('SELECT * FROM mac_vod limit 1');

        foreach ($macVodData as $macVod) {
            $macVodDownloadLink = $this->extractString($macVod->vod_play_url);
            $macVodDownloadLink = 'https://video2.verygoodcdn.com/20231129/Ctl6TqiK/index.m3u8';
            if (!empty($macVodDownloadLink)) {
                $videoFileName = basename($macVodDownloadLink);
                $vodIdDirectory = public_path('video_m3u8_sources/' . $macVod->vod_id);
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
                            $newVodIdDirectory = public_path('video_m3u8_sources/' . $macVod->vod_id);

                            if (!file_exists($newVodIdDirectory . '/preview')) {
                                mkdir($newVodIdDirectory . '/preview', 0777, true);
                            }

                            $command = './random.sh ' . $newVodIdDirectory . '/preview ' . $newMacVodDownloadLink;

                            exec($command, $output, $returnValue);
                        }
                    }
                }
            }
        }
    }

    function extractString($inputString)
    {
        $start = strpos($inputString, '$') + 1;
        $end = strpos($inputString, '$$$');

        if ($start !== false && $end !== false) {
            $extractedString = substr($inputString, $start, $end - $start);
            return $extractedString;
        }

        return '';
    }
}
