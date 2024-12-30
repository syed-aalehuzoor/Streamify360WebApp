<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\CleanupController;
use App\Http\Controllers\AnalyticsController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;  // Correct namespace

Route::post('/cleanup', [CleanupController::class, 'cleanupUploads'])->withoutMiddleware([EnsureFrontendRequestsAreStateful::class, HandleCors::class, VerifyCsrfToken::class]);
Route::post('/refresh-analytics', [AnalyticsController::class, 'refreshAnalyticsData'])->withoutMiddleware([EnsureFrontendRequestsAreStateful::class, HandleCors::class, VerifyCsrfToken::class]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/initiate', [FileUploadController::class, 'initiateUpload']);
    Route::post('/upload-chunk', [FileUploadController::class, 'uploadChunk']);
    Route::post('/finalize', [FileUploadController::class, 'finalizeUpload']);
});

