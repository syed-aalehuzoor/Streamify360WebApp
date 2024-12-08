<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public $chunkSize = 1024 * 1024;

    public function initiateUpload(Request $request)
    {
        $upload_id =  'user1_' . uniqid('upload_', true);
        
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

        $finalchunkPath = Storage::path("chunks/{$upload_id}.part{$chunkIndex}");
        $tempFilepath = $chunk->getRealPath();
        $buff = file_get_contents($tempFilepath);

        $final = fopen($finalchunkPath, 'wb');
        fwrite($final, $buff);
        fclose($final);
        unlink($tempFilepath);
        
        return response()->json(['success' => true]);
    }
    
    public function finalizeUpload(Request $request)
    {
        $upload_id = $request->input('upload_id');
        $totalChunks = $request->input('totalChunks');
        $fileExtension = $request->input('fileExtension');

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
        
        // Return the relative filepath for storage
        return response()->json([
            'success' => true,
        ]);
    }
}
