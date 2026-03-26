<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResidentOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user()->loadMissing('residentProfile.verification');

        $residentProfile = $user->residentProfile;
        $verification = $residentProfile?->verification;

        if (! $residentProfile || ! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        if ($verification->status === 'verified') {
            return $next($request);
        }

        if ($verification->status === 'pending_verification') {
            return redirect()->route('resident.verification.pending');
        }

        if ($verification->status === 'needs_correction') {
            return redirect()->route('resident.verification.correction');
        }

        if ($verification->status === 'rejected') {
            return redirect()->route('resident.verification.rejected');
        }

        return redirect()->route('resident.onboarding.create');
    }
}
