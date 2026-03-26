<?php

use App\Http\Middleware\EnsureBarangayOfficialRole;
use App\Http\Middleware\EnsureBarangayPermission;
use App\Http\Middleware\EnsureCitySuperAdminRole;
use App\Http\Middleware\EnsureResidentOnboardingComplete;
use App\Http\Middleware\EnsureResidentRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'resident' => EnsureResidentRole::class,
            'resident.onboarding' => EnsureResidentOnboardingComplete::class,
            'barangay_official' => EnsureBarangayOfficialRole::class,
            'official.permission' => EnsureBarangayPermission::class,
            'super_admin' => EnsureCitySuperAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
