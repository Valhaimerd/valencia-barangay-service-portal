<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_residents' => User::query()->where('role', 'resident')->count(),
            'verified_residents' => User::query()->where('role', 'resident')->where('is_resident_verified', true)->count(),
            'total_officials' => User::query()->where('role', 'barangay_official')->count(),
            'total_requests' => ServiceRequest::query()->count(),
            'document_requests' => ServiceRequest::query()->where('request_category', 'document')->count(),
            'assistance_requests' => ServiceRequest::query()->where('request_category', 'assistance')->count(),
        ];

        $barangaySummaries = Barangay::query()
            ->withCount([
                'residentProfiles as resident_count',
                'officialProfiles as official_count',
                'serviceRequests as request_count',
            ])
            ->orderBy('name')
            ->get();

        $serviceSummaries = ServiceType::query()
            ->withCount('serviceRequests')
            ->orderByDesc('service_requests_count')
            ->orderBy('name')
            ->get();

        $recentRequests = ServiceRequest::query()
            ->with(['serviceType', 'barangay'])
            ->latest('submitted_at')
            ->limit(12)
            ->get();

        return view('super-admin.reports.index', [
            'stats' => $stats,
            'barangaySummaries' => $barangaySummaries,
            'serviceSummaries' => $serviceSummaries,
            'recentRequests' => $recentRequests,
        ]);
    }
}
