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
use App\Http\Controllers\AbuseReports;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Servers;

Route::get('/video/{id}', [PlayerController::class, 'show'])
    ->middleware([EnforcePlaybackDomain::class])
    ->name('video.player');

Route::get('/{id}/report', [AbuseReports::class, 'create'])->name('abuse.report');

Route::get('/test', [HomeController::class, 'testanalytics']);

Route::post('/report', [AbuseReports::class, 'store'])->name('abuse-reports.store');

Route::middleware(['enforceMainDomain'])->group(function () {
    Route::get('/temp-login/{token}', function ($token) {
        $userId = \Illuminate\Support\Facades\Cache::pull("temp_login_{$token}");    
        if (!$userId) {
            abort(403, 'Invalid or expired token.');
        }
        Auth::loginUsingId($userId);
        return redirect('/dashboard')->with('message', 'Logged in via temporary link.');
    });

    Route::get('/', function () {return view('welcome');});
    
    Route::get('/test/cloudflare', [CloudflareTestController::class, 'index'])->name('test.cloudflare.form');
    Route::post('/test/cloudflare', [CloudflareTestController::class, 'handleRequest'])->name('test.cloudflare');
    
    Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'google_callback']);
    
    Route::middleware(
        ['auth:sanctum',config('jetstream.auth_session'),'verified', 'checkUserStatus']
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
    
            Route::prefix('plan')->group(function (){
                Route::get('/subscription', [SubscriptionPlanController::class, 'index'])->name('subscription');
            });
    
            Route::group([
                'middleware' => ['admin'],
                'prefix' => 'admin'
            ], function ()
            {
                Route::resource('system-settings', \App\Http\Controllers\Admin\Settings::class);   
                Route::get('/users/{id}/temp-login', [UserController::class, 'tempLogin'])->name('admin.temp-login');
                Route::get('', [AdminDashboardController::class, 'dashboard'])->name('admin');
    
                Route::resource('videos', VideoAdminController::class)
                    ->names([
                        'index' => 'admin-videos.index',
                        'create' => 'admin-videos.create',
                        'store' => 'admin-videos.store',
                        'show' => 'admin-videos.show',
                        'edit' => 'admin-videos.edit',
                        'update' => 'admin-videos.update',
                        'destroy' => 'admin-videos.destroy',
                    ]);            
            
                Route::resource('users', UserController::class);
                Route::resource('abuse-reports', AbuseReports::class)->except(['store', 'create', 'destroy']);
                Route::delete('/videos/{videoId}/reports/{reportId?}', [AbuseReports::class, 'destroy'])->name('abuse-reports.destroy');
                Route::resource('servers', Servers::class);
        });
    });    

});