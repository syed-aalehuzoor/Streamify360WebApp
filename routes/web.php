<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\AnalyticsController;
use App\Helpers\EncoderApiAuth;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Settings\PlayerSettingsController;
use App\Http\Controllers\Settings\AdSettingsController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Livewire\PlayerComponent;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Settings\CustomDomainController;
use App\Http\Controllers\Settings\UserSettingsController;
use App\Http\Controllers\CloudflareTestController;
use App\Http\Middleware\EnforceMainDomain;
use App\Http\Middleware\EnforcePlaybackDomain;
use App\Http\Controllers\Admin\VideoAdminController;

Route::get('/video/{id}', [PlayerController::class, 'show'])
    ->middleware([EnforcePlaybackDomain::class])
    ->name('video.player');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return view('test');
});

Route::get('/test/cloudflare', [CloudflareTestController::class, 'index'])->name('test.cloudflare.form');
Route::post('/test/cloudflare', [CloudflareTestController::class, 'handleRequest'])->name('test.cloudflare');

Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'google_callback']);

Route::middleware(
    ['auth:sanctum',config('jetstream.auth_session'),'verified', 'enforceMainDomain']
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
        
        Route::resource('settings', UserSettingsController::class);

        /*
        Route::prefix('settings')->group(function (){
            Route::get('/player', [PlayerSettingsController::class, 'edit'])->name('player-settings.edit');
            Route::post('/player', [PlayerSettingsController::class, 'update'])->name('player-settings.update');
        }); 
        */
        Route::prefix('plan')->group(function (){
            Route::get('/subscription', [SubscriptionPlanController::class, 'index'])->name('subscription');
        });

        Route::middleware(
            ['userplan:premium',]
            )->group(function () 
            {
                //Users Subscription Routes
                Route::get('/settings/ads', [AdSettingsController::class, 'edit'])->name('ad-settings');
                Route::post('/settings/ads', [AdSettingsController::class, 'update'])->name('ad-settings.update');

                Route::get('/settings/custom-domain', [CustomDomainController::class, 'edit'])->name('custom-domain');
                Route::post('/settings/custom-domain', [CustomDomainController::class, 'save'])->name('custom-domain.update');

                Route::get('/tools/sub', [SettingsController::class, 'general'])->name('subtitle-translator');
                Route::get('/tools', [SettingsController::class, 'general'])->name('tools');
        });

        Route::group([
            'middleware' => ['admin'],
            'prefix' => 'admin'
        ], function ()
        {

            //Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        
            // Dashboard Routes
            Route::get('', [AdminDashboardController::class, 'dashboard'])->name('admin');

            Route::resource('admin-videos', VideoAdminController::class);

            Route::get('/abuse-reports', [AdminDashboardController::class, 'abuseReports'])->name('abuse-reports');
        
            // Server Manager Routes
            Route::get('/servers', [ServerController::class, 'index'])->name('admin-servers');
            Route::get('/servers/add', [ServerController::class, 'create'])->name('admin-add-server');
            Route::post('/servers', [ServerController::class, 'store'])->name('admin-store-server');
        
            Route::resource('users', UserController::class);

            Route::post('/video-settings', [ServerController::class, 'store'])->name('video-settings');
            Route::get('/system-config', [AdminSettingsController::class, 'index'])->name('config-setting');
            Route::post('/system-config', [AdminSettingsController::class, 'update'])->name('config-setting.update');

    });
});

