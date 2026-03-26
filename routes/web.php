<?php

use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $featuredServices = ServiceType::query()
        ->where('is_active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->limit(6)
        ->get();

    return view('welcome', [
        'featuredServices' => $featuredServices,
    ]);
})->name('home');

Route::get('/services', function () {
    $services = ServiceType::query()
        ->where('is_active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->groupBy('category');

    return view('site.services.index', [
        'services' => $services,
    ]);
})->name('services.index');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user()->loadMissing([
        'officialProfile',
        'residentProfile.verification',
    ]);

    if ($user->isCitySuperAdmin()) {
        return redirect()->route('super_admin.dashboard');
    }

    if ($user->isBarangayOfficial()) {
        return redirect()->route($user->preferredBarangayRoute());
    }

    if ($user->isResident()) {
        $residentProfile = $user->residentProfile;
        $verification = $residentProfile?->verification;

        if (! $residentProfile || ! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        return match ($verification->status) {
            'verified' => redirect()->route('resident.dashboard'),
            'pending_verification' => redirect()->route('resident.verification.pending'),
            'needs_correction' => redirect()->route('resident.verification.correction'),
            'rejected' => redirect()->route('resident.verification.rejected'),
            default => redirect()->route('resident.onboarding.create'),
        };
    }

    return redirect()->route('home');
})->middleware('auth')->name('dashboard');

require __DIR__ . '/auth.php';
require __DIR__ . '/resident.php';
require __DIR__ . '/barangay.php';
require __DIR__ . '/super_admin.php';
