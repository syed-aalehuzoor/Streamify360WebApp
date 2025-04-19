<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AzureBatchService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Video;
use DateTime;
use Exception;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $videoId;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(30);
    }

    public function handle(AzureBatchService $azureBatchService)
    {
        $video = Video::find($this->videoId);
        if(!$video->is_blob_file){
            return $this->release(20);
        }
        $server = $video->server;
        $base_url = 'https://' . config('services.azure.storage_name') . '.blob.core.windows.net/' . config('services.azure.storage_container') . '/';
        $video_ext = pathinfo($video->video_url, PATHINFO_EXTENSION) ?: 'mp4';
        $command = "python3 run.py --key {$this->videoId} --domain {$server->domain} --serverip {$server->ip} --max_workers 16 --video video.mp4";

        $resourceFiles = [
            [
                'filePath' => "video.{$video_ext}",
                'httpUrl'  => "{$base_url}{$video->video_url}",
            ],
            [
                'filePath' => "run.py",
                'httpUrl'  => "https://hlsencoder.blob.core.windows.net/scripts/run.py?sp=r&st=2024-11-12T13:51:25Z&se=2025-11-10T21:51:25Z&spr=https&sv=2022-11-02&sr=b&sig=nLtpVN%2FitaWF6hreT3nFI4NW%2FJ0ttlEpvf5P4CbOPTU%3D",
            ],
            [
                'filePath' => "streamify360.pem",
                'httpUrl'  => "https://hlsencoder.blob.core.windows.net/scripts/streamify360.pem?sp=r&st=2024-11-12T13:54:24Z&se=2025-11-10T21:54:24Z&spr=https&sv=2022-11-02&sr=b&sig=3T7YjhC7wHRyOLCDweFhQAtd4EMT7zUpbaQMn2Yspxs%3D",
                'fileMode' => '0400'
            ],
        ];

        if ($video->thumbnail_url) {
            $thumb_ext = pathinfo($video->thumbnail_url, PATHINFO_EXTENSION) ?: 'mp4';
            $resourceFiles[] = [
                'filePath' => "thumbnail.{$thumb_ext}",
                'httpUrl'  => "{$base_url}{$video->thumbnail_url}",
            ];
            $command .= " --thumbnail " . "thumbnail.{$thumb_ext}";
        }
        $jobId = 'Job1';
        $azureBatchService->addTask($jobId, $this->videoId, $command, $resourceFiles);
        VideoProcessCheck::dispatch($this->videoId);
    }

    public function failed(\Throwable $exception)
    {
        $video = Video::where('id', $this->videoId)->first();
        $video->update(['status' => 'Failed']);
        Log::error('Job failed: ' . $exception->getMessage());
    }
}
