<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\OfficialProfile;
use App\Models\ResidentVerification;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_officials' => OfficialProfile::query()->count(),
            'active_officials' => OfficialProfile::query()->where('is_active', true)->count(),
            'total_residents' => User::query()->where('role', 'resident')->count(),
            'pending_verifications' => ResidentVerification::query()->where('status', 'pending_verification')->count(),
            'active_barangays' => Barangay::query()->where('is_active', true)->count(),
        ];

        $recentOfficials = OfficialProfile::query()
            ->with(['user', 'barangay', 'assignedBy'])
            ->latest('assigned_at')
            ->limit(5)
            ->get();

        return view('super-admin.dashboard', [
            'stats' => $stats,
            'recentOfficials' => $recentOfficials,
        ]);
    }
}
