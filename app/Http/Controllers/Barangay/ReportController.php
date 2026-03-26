<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $barangayId = $user->officialProfile?->barangay_id;
        abort_unless($barangayId, 403, 'This official account has no barangay assignment.');

        $documentStatusCounts = collect(config('portal.document_request_statuses'))
            ->map(fn ($label, $status) => [
                'status' => $status,
                'label' => $label,
                'count' => ServiceRequest::query()
                    ->where('barangay_id', $barangayId)
                    ->where('request_category', 'document')
                    ->where('current_status', $status)
                    ->count(),
            ])
            ->values();

        $assistanceStatusCounts = collect(config('portal.assistance_request_statuses'))
            ->map(fn ($label, $status) => [
                'status' => $status,
                'label' => $label,
                'count' => ServiceRequest::query()
                    ->where('barangay_id', $barangayId)
                    ->where('request_category', 'assistance')
                    ->where('current_status', $status)
                    ->count(),
            ])
            ->values();

        $dailySubmissions = ServiceRequest::query()
            ->selectRaw('DATE(submitted_at) as report_date, COUNT(*) as total')
            ->where('barangay_id', $barangayId)
            ->whereNotNull('submitted_at')
            ->groupBy(DB::raw('DATE(submitted_at)'))
            ->orderByDesc(DB::raw('DATE(submitted_at)'))
            ->limit(14)
            ->get();

        $recentRequests = ServiceRequest::query()
            ->with(['residentProfile.user', 'serviceType'])
            ->where('barangay_id', $barangayId)
            ->latest('latest_status_at')
            ->limit(12)
            ->get();

        return view('barangay.reports.index', [
            'barangay' => $user->officialProfile?->barangay,
            'documentStatusCounts' => $documentStatusCounts,
            'assistanceStatusCounts' => $assistanceStatusCounts,
            'dailySubmissions' => $dailySubmissions,
            'recentRequests' => $recentRequests,
        ]);
    }
}
