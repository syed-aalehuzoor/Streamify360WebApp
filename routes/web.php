<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\AnalyticsController;
use App\Helpers\EncoderApiAuth;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\PlayerSettingsController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Livewire\PlayerComponent;
use App\Http\Controllers\FileUploadController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test3', [AnalyticsController::class, 'refreshAnalyticsData']); 
Route::get('/test', [HomeController::class, 'test']);     
Route::get('/test2', function () {
    return view('test2');
});     
Route::get('/test4', [FileUploadController::class, 'cleanupUploads']);     

Route::get('/video/{id}', PlayerComponent::class)->name('video.player');

Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'google_callback']);

Route::middleware(
    ['auth:sanctum',config('jetstream.auth_session'),'verified']
    )->group(function ()
    {
        Route::get('/home', [HomeController::class, 'index']);
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        
        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get('/', [VideoController::class, 'index'])                      ->name('index');
            Route::get('/drafts', [VideoController::class, 'drafts'])               ->name('drafts');    
            Route::get('/upload-new',[VideoController::class, 'create'])            ->name('add-new');
            Route::get('/{id}/edit',[VideoController::class, 'read'])               ->name('edit');
            Route::post('/{id}/edit',[VideoController::class, 'update'])            ->name('post-edit');
            Route::post('/bulk-delete',[VideoController::class, 'bulkDelete'])    ->name('bulk-delete');
            Route::delete('/{id}',[VideoController::class, 'delete'])               ->name('destroy');
        });

        Route::prefix('analytics')->group(function () {
            Route::get('/performance-videos', [AnalyticsController::class, 'listPerformanceVideos'])->name('performance-videos-list');
            Route::get('/performance-videos/{id}', [AnalyticsController::class, 'showVideoPerformance'])->name('video-performance-trend');
            Route::get('/audience-videos', [AnalyticsController::class, 'listAudienceVideos'])->name('audience-videos-list');
            Route::get('/audience-videos/{id}', [AnalyticsController::class, 'showVideoAudienceInsights'])->name('video-audience-insights');
        });
        

        Route::prefix('settings')->group(function (){
            Route::get('/player', [PlayerSettingsController::class, 'edit'])->name('player-settings.edit');
            Route::post('/player', [PlayerSettingsController::class, 'update'])->name('player-settings.update');
        });
        Route::prefix('plan')->group(function (){
            Route::get('/subscription', [SubscriptionPlanController::class, 'index'])->name('subscription');
        });

        Route::middleware(
            ['userplan:premium',]
            )->group(function () 
            {
                //Users Subscription Routes
                Route::get('/settings/ads', [SettingsController::class, 'ads_settings'])->name('ad-settings');
                Route::post('/settings/ads', [SettingsController::class, 'update'])->name('ad-settings.update');
                Route::get('/tools/sub', [SettingsController::class, 'general'])->name('subtitle-translator');
                Route::get('/tools', [SettingsController::class, 'general'])->name('tools');
        });

        Route::group([
            'middleware' => ['admin'],
            'prefix' => 'admin'
        ], function ()
        {

            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        
            // Dashboard Routes
            Route::get('', [AdminDashboardController::class, 'dashboard'])->name('admin');
            Route::get('/videos', [AdminDashboardController::class, 'videos'])->name('admin-all-videos');
            Route::get('/processes', [AdminDashboardController::class, 'processes'])->name('Processes');
            Route::get('/abuse-reports', [AdminDashboardController::class, 'abuseReports'])->name('abuse-reports');
        
            // Server Manager Routes
            Route::get('/servers', [ServerController::class, 'index'])->name('admin-servers');
            Route::get('/servers/add', [ServerController::class, 'create'])->name('admin-add-server');
            Route::post('/servers', [ServerController::class, 'store'])->name('admin-store-server');
        
            Route::prefix('users')->group(function ()
            {                
                // User Manager Routes
                Route::get('/', [UserController::class, 'index'])->name('admin-users');
                Route::post('/', [UserController::class, 'store'])->name('users.store');
                Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
                Route::patch('/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
                Route::patch('/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
            });

            Route::post('/video-settings', [ServerController::class, 'store'])->name('video-settings');
            Route::get('/system-config', [AdminSettingsController::class, 'index'])->name('config-setting');
            Route::post('/system-config', [AdminSettingsController::class, 'update'])->name('config-setting.update');

    });
});

