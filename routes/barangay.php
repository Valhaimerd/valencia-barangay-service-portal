<?php

use App\Http\Controllers\Barangay\AssistanceRequestController;
use App\Http\Controllers\Barangay\DashboardController;
use App\Http\Controllers\Barangay\DocumentRequestController;
use App\Http\Controllers\Barangay\GeneratedDocumentController;
use App\Http\Controllers\Barangay\PaymentOperationController;
use App\Http\Controllers\Barangay\ReferralOperationController;
use App\Http\Controllers\Barangay\ReleaseOperationController;
use App\Http\Controllers\Barangay\ReportController;
use App\Http\Controllers\Barangay\ResidentVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'barangay_official'])
    ->prefix('barangay')
    ->name('barangay.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware('official.permission:reports')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        });

        Route::middleware('official.permission:verification_review')->group(function () {
            Route::get('/verifications', [ResidentVerificationController::class, 'index'])->name('verifications.index');
            Route::get('/verifications/{residentVerification}', [ResidentVerificationController::class, 'show'])->name('verifications.show');
            Route::put('/verifications/{residentVerification}', [ResidentVerificationController::class, 'update'])->name('verifications.update');
        });

        Route::middleware('official.permission:request_processing')->group(function () {
            Route::get('/documents', [DocumentRequestController::class, 'index'])->name('documents.index');
            Route::get('/documents/{serviceRequest}', [DocumentRequestController::class, 'show'])->name('documents.show');
            Route::put('/documents/{serviceRequest}', [DocumentRequestController::class, 'update'])->name('documents.update');
            Route::get('/documents/{serviceRequest}/print', [GeneratedDocumentController::class, 'show'])->name('documents.print');

            Route::get('/assistance', [AssistanceRequestController::class, 'index'])->name('assistance.index');
            Route::get('/assistance/{serviceRequest}', [AssistanceRequestController::class, 'show'])->name('assistance.show');
            Route::put('/assistance/{serviceRequest}', [AssistanceRequestController::class, 'update'])->name('assistance.update');
        });

        Route::middleware('official.permission:payment_processing')->group(function () {
            Route::get('/payments', [PaymentOperationController::class, 'index'])->name('payments.index');
            Route::get('/payments/{serviceRequest}', [PaymentOperationController::class, 'show'])->name('payments.show');
            Route::put('/payments/{serviceRequest}', [PaymentOperationController::class, 'update'])->name('payments.update');
        });

        Route::middleware('official.permission:release_processing')->group(function () {
            Route::get('/releases', [ReleaseOperationController::class, 'index'])->name('releases.index');
            Route::get('/releases/{serviceRequest}', [ReleaseOperationController::class, 'show'])->name('releases.show');
            Route::put('/releases/{serviceRequest}', [ReleaseOperationController::class, 'update'])->name('releases.update');
        });

        Route::middleware('official.permission:referral_processing')->group(function () {
            Route::get('/referrals', [ReferralOperationController::class, 'index'])->name('referrals.index');
            Route::get('/referrals/{serviceRequest}', [ReferralOperationController::class, 'show'])->name('referrals.show');
            Route::put('/referrals/{serviceRequest}', [ReferralOperationController::class, 'update'])->name('referrals.update');
        });
    });
