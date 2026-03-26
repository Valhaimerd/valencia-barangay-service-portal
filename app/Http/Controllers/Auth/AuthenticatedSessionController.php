<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user()->loadMissing([
            'residentProfile.verification',
            'officialProfile',
        ]);

        if ($user->account_status !== 'active') {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'Your account is not active. Please contact the administrator.',
                ])
                ->onlyInput('email');
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        if ($user->isCitySuperAdmin()) {
            return redirect()->intended(route('super_admin.dashboard', absolute: false));
        }

        if ($user->isBarangayOfficial()) {
            return redirect()->intended(route($user->preferredBarangayRoute(), absolute: false));
        }

        if ($user->isResident()) {
            $residentProfile = $user->residentProfile;
            $verification = $residentProfile?->verification;

            if (! $residentProfile || ! $verification) {
                return redirect()->route('resident.onboarding.create');
            }

            return match ($verification->status) {
                'verified' => redirect()->intended(route('resident.dashboard', absolute: false)),
                'pending_verification' => redirect()->route('resident.verification.pending'),
                'needs_correction' => redirect()->route('resident.verification.correction'),
                'rejected' => redirect()->route('resident.verification.rejected'),
                default => redirect()->route('resident.onboarding.create'),
            };
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
