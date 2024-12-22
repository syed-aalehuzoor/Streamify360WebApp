<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    FileUploadController::cleanupUploads();
})->everyMinute();

Schedule::call(function () {
    AnalyticsController::updateViewsOverTime();
})->hourly();