<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use App\Models\Video;
use App\Models\Server;
use Exception;


class DownloadVideo implements ShouldQueue
{
    use Queueable;
    public $video_id;

    /**
     * Create a new job instance.
     */
    public function __construct($video_id)
    {
        $this->video_id = $video_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $video = Video::find($this->video_id);
        $response = Http::post('http://127.0.0.1:5000/api/download_video/'.$this->video_id);
        if ($response->failed()) {
            throw new Exception('Failed to delete video');
        }
    }
}
