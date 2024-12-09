<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Define the test API route
Route::get('/test-api', function (Request $request) {
    return response()->json([
        'message' => 'This is a test API response.',
        'status' => 'success',
    ]);
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/initiate', [FileUploadController::class, 'initiateUpload']);
    Route::post('/upload-chunk', [FileUploadController::class, 'uploadChunk']);
    Route::post('/finalize', [FileUploadController::class, 'finalizeUpload']);
});

