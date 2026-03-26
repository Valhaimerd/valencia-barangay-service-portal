<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        return view('resident.profile.show', [
            'user' => $user,
            'residentProfile' => $user->residentProfile,
            'verification' => $user->residentProfile?->verification,
        ]);
    }
}
