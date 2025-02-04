<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Exception;

class DeleteVideo implements ShouldQueue
{
    use Queueable;
    protected $videoId;

    /**
     * Create a new job instance.
     *
     * @param  int  $videoId
     * @return void
     */
    public function __construct($videoId)
    {
        $this->videoId = $videoId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $response = Http::post('http://127.0.0.1:5000/api/delete_video/'.$this->videoId);

            // Optionally check if the request was successful
            if ($response->failed()) {
                throw new Exception('Failed to delete video');
            }
        } catch (Exception $e) {
            // The job will automatically retry if it fails (we'll set retry logic next).
            throw $e;
        }
    }

    /**
     * Determine the number of seconds before the job should be retried.
     *
     * @param  \Exception  $exception
     * @return int
     */
    public function retryUntil()
    {
        return now()->addMinutes(3);
    }

    public function backoff()
    {
        return [ 60 , 60 * 60 , 60 * 60 * 6];
    }

    /**
     * Get the number of retries for this job.
     *
     * @return int
     */
    public function tries()
    {
        return 3;  // Retry up to 3 times
    }

    /**
     * Handle job failure.
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Log failure or send a notification if needed
        \Log::error("Failed to delete video with ID {$this->videoId}: " . $exception->getMessage());
    }
}
    