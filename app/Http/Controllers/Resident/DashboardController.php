<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification',
        ]);

        $residentProfile = $user->residentProfile;

        $requestStats = [
            'total' => 0,
            'documents' => 0,
            'assistance' => 0,
            'completed' => 0,
        ];

        $recentRequests = collect();

        if ($residentProfile) {
            $baseQuery = ServiceRequest::query()
                ->with(['serviceType'])
                ->where('resident_profile_id', $residentProfile->id);

            $requestStats = [
                'total' => (clone $baseQuery)->count(),
                'documents' => (clone $baseQuery)->where('request_category', 'document')->count(),
                'assistance' => (clone $baseQuery)->where('request_category', 'assistance')->count(),
                'completed' => (clone $baseQuery)->whereIn('current_status', ['released', 'closed'])->count(),
            ];

            $recentRequests = (clone $baseQuery)
                ->latest('created_at')
                ->limit(5)
                ->get();
        }

        return view('resident.dashboard', [
            'user' => $user,
            'residentProfile' => $residentProfile,
            'verification' => $residentProfile?->verification,
            'requestStats' => $requestStats,
            'recentRequests' => $recentRequests,
        ]);
    }
}
