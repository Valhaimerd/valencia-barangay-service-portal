<?php

use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\OfficialManagementController;
use App\Http\Controllers\SuperAdmin\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'super_admin'])
    ->prefix('super-admin')
    ->name('super_admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

        Route::get('/officials', [OfficialManagementController::class, 'index'])->name('officials.index');
        Route::get('/officials/create', [OfficialManagementController::class, 'create'])->name('officials.create');
        Route::post('/officials', [OfficialManagementController::class, 'store'])->name('officials.store');
        Route::get('/officials/{official}/edit', [OfficialManagementController::class, 'edit'])->name('officials.edit');
        Route::put('/officials/{official}', [OfficialManagementController::class, 'update'])->name('officials.update');
    });
