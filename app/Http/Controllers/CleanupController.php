<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Video;
use Carbon\Carbon;

class CleanupController extends Controller
{
        /**
     * Delete failed videos older than 1 day
     * 
     * @return \Illuminate\Http\JsonResponse
     * 
     */
    public function deleteFailedVideos() {
        $videos = Video::where('status', 'Failed')->get();
        foreach ($videos as $video) {
            $now = new \DateTime();
            $diff = $now->diff($video->created_at);
            if ($diff->days >= 1) {
                $video->delete();
            }
        }
        return response()->json(['message' => 'Failed videos deleted successfully.'], 200);
    }

    public function cleanupUploads()
    {
        // Get all files in the chunks directory
        $files = Storage::files('chunks');
        $oneHourAgo = now()->subHour();

        foreach ($files as $file) {
            // Extract upload_id from filename
            if (preg_match('/^chunks\/(user1_upload_[a-f0-9\.]+)/', $file, $matches)) {
                $upload_id = $matches[1];
                
                // Check if there's a cache entry for this upload
                $upload = Cache::get("upload:{$upload_id}");
                
                // Delete files if:
                // 1. Cache entry doesn't exist (completed upload) and file is older than 1 hour
                // 2. Cache entry exists but is older than 1 hour (stale upload)
                if ((!$upload && Storage::lastModified($file) < $oneHourAgo->timestamp) ||
                    ($upload && $upload['created_at']->lt($oneHourAgo))) {
                    
                    Storage::delete($file);
                    Cache::forget("upload:{$upload_id}");
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Cleanup completed']);
    }
}
