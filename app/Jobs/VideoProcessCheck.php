<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\AzureBatchService;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use DateTime;
use Exception;

class VideoProcessCheck implements ShouldQueue
{

    use Dispatchable, Queueable;

    protected $videoId;

    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }
    
    public function retryUntil(): DateTime
    {
        return now()->addHours(3);
    }

    /**
     * Execute the job.
     */
    public function handle(AzureBatchService $azureBatchService)
    {
        $video = Video::find($this->videoId);
        $taskDetails = $azureBatchService->checkTaskStatus('Job1', $this->videoId);
        if ($taskDetails['state'] == 'running' || $taskDetails['state'] == 'active')
        {
            return $this->release(60);
        }
        elseif ($taskDetails['state'] == 'failed'){
            $video->update(['state' => 'Failed']);
        }
        elseif ($taskDetails['state'] == 'completed')
        {
            $video->update(['manifest_url' => 'master.m3u8']);
            $video->update(['status' => 'live']);    
        }
        else{
            Log::error('Unknown state:'. $taskDetails['state']);
        }
    }
}
