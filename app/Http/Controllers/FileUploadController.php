<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class FileUploadController extends Controller
{
    public $chunkSize = 1024 * 1024;

    public function initiateUpload(Request $request)
    {
        $upload_id =  'user1_' . uniqid('upload_', true);
        
        Cache::put("upload:{$upload_id}", [
            'created_at' => now(),
            'chunks_received' => []
        ], now()->addDay());

        return response()->json([
            'success' => true,
            'upload_id' => $upload_id
        ]);
    }

    public function uploadChunk(Request $request)
    {
        $chunk = $request->file('file');
        $upload_id = $request->input('upload_id');
        $chunkIndex = $request->input('chunkIndex');
        
        $upload = Cache::get("upload:{$upload_id}");
        if (!$upload) {
            return response()->json(['error' => 'Upload session expired or invalid'], 400);
        }

        $finalchunkPath = Storage::path("chunks/{$upload_id}.part{$chunkIndex}");
        $tempFilepath = $chunk->getRealPath();
        $buff = file_get_contents($tempFilepath);

        $final = fopen($finalchunkPath, 'wb');
        fwrite($final, $buff);
        fclose($final);
        unlink($tempFilepath);
        
        $upload['chunks_received'][] = $chunkIndex;
        Cache::put("upload:{$upload_id}", $upload, now()->addDay());

        return response()->json(['success' => true]);
    }
    
    public function finalizeUpload(Request $request)
    {
        $upload_id = $request->input('upload_id');
        $totalChunks = $request->input('totalChunks');
        $fileExtension = $request->input('fileExtension');
        $upload = Cache::get("upload:{$upload_id}");
        if (!$upload) {
            return response()->json(['error' => 'Missing chunks or invalid upload'], 400);
        }

        // Combine chunks
        $finalPath = Storage::path("chunks/{$upload_id}.{$fileExtension}");
        $finalFile = fopen($finalPath, 'wb');
        
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = Storage::path("chunks/{$upload_id}.part{$i}");
            $chunk = file_get_contents($chunkPath);
            fwrite($finalFile, $chunk);
            unlink($chunkPath); // Delete chunk after combining
        }
        
        fclose($finalFile);
        Cache::forget("upload:{$upload_id}");
        // Return the relative filepath for storage
        return response()->json([
            'success' => true,
        ]);
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
