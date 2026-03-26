<?php

use App\Http\Controllers\Resident\DashboardController;
use App\Http\Controllers\Resident\NotificationController;
use App\Http\Controllers\Resident\OnboardingController;
use App\Http\Controllers\Resident\ProfileController;
use App\Http\Controllers\Resident\RequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'resident'])
    ->prefix('resident')
    ->name('resident.')
    ->group(function () {
        Route::get('/onboarding', [OnboardingController::class, 'create'])->name('onboarding.create');
        Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
        Route::get('/onboarding/edit', [OnboardingController::class, 'edit'])->name('onboarding.edit');
        Route::put('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

        Route::get('/verification/pending', [OnboardingController::class, 'pending'])->name('verification.pending');
        Route::get('/verification/correction', [OnboardingController::class, 'correction'])->name('verification.correction');
        Route::get('/verification/rejected', [OnboardingController::class, 'rejected'])->name('verification.rejected');

        Route::middleware('resident.onboarding')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');

            Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
            Route::get('/requests/create/{serviceType:code}', [RequestController::class, 'createForService'])->name('requests.create.service');
            Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');

            Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
            Route::get('/requests/{referenceNumber}', [RequestController::class, 'show'])->name('requests.show');
        });
    });
