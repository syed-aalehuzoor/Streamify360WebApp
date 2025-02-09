<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;  // Correct namespace
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\FileUploadController;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            HandleCors::class,
            VerifyCsrfToken::class,
        ]);

        $middleware->alias([
            'admin' => App\Http\Middleware\Admin::class,
            'userplan' => App\Http\Middleware\UserPlan::class,
            'enforceMainDomain' => App\Http\Middleware\EnforceMainDomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
