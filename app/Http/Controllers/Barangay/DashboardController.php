<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\ResidentVerification;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $officialProfile = $user->officialProfile;
        $barangay = $officialProfile?->barangay;
        $barangayId = $officialProfile?->barangay_id;

        $stats = [
            'pending_verifications' => 0,
            'needs_correction' => 0,
            'verified_residents' => 0,
            'rejected_verifications' => 0,
            'total_requests' => 0,
            'document_requests' => 0,
            'ready_for_pickup_documents' => 0,
            'assistance_requests' => 0,
            'for_assessment_assistance' => 0,
            'ready_for_claim_assistance' => 0,
            'for_payment_documents' => 0,
            'referred_assistance' => 0,
            'releasable_total' => 0,
        ];

        $recentVerifications = collect();
        $recentDocumentRequests = collect();
        $recentAssistanceRequests = collect();

        if ($barangayId) {
            $verificationQuery = ResidentVerification::query()
                ->whereHas('residentProfile', fn ($query) => $query->where('barangay_id', $barangayId));

            $documentQuery = ServiceRequest::query()
                ->where('barangay_id', $barangayId)
                ->where('request_category', 'document');

            $assistanceQuery = ServiceRequest::query()
                ->where('barangay_id', $barangayId)
                ->where('request_category', 'assistance');

            $readyForPickupDocuments = (clone $documentQuery)->where('current_status', 'ready_for_pickup')->count();
            $readyForClaimAssistance = (clone $assistanceQuery)->where('current_status', 'ready_for_claim')->count();

            $stats = [
                'pending_verifications' => (clone $verificationQuery)->where('status', 'pending_verification')->count(),
                'needs_correction' => (clone $verificationQuery)->where('status', 'needs_correction')->count(),
                'verified_residents' => (clone $verificationQuery)->where('status', 'verified')->count(),
                'rejected_verifications' => (clone $verificationQuery)->where('status', 'rejected')->count(),
                'total_requests' => ServiceRequest::query()->where('barangay_id', $barangayId)->count(),
                'document_requests' => (clone $documentQuery)->count(),
                'ready_for_pickup_documents' => $readyForPickupDocuments,
                'assistance_requests' => (clone $assistanceQuery)->count(),
                'for_assessment_assistance' => (clone $assistanceQuery)->where('current_status', 'for_assessment')->count(),
                'ready_for_claim_assistance' => $readyForClaimAssistance,
                'for_payment_documents' => (clone $documentQuery)->where('current_status', 'for_payment')->count(),
                'referred_assistance' => (clone $assistanceQuery)->where('current_status', 'referred')->count(),
                'releasable_total' => $readyForPickupDocuments + $readyForClaimAssistance,
            ];

            $recentVerifications = (clone $verificationQuery)
                ->with(['residentProfile.user', 'residentProfile.barangay'])
                ->latest('submitted_at')
                ->limit(6)
                ->get();

            $recentDocumentRequests = (clone $documentQuery)
                ->with(['residentProfile.user', 'serviceType'])
                ->latest('submitted_at')
                ->limit(6)
                ->get();

            $recentAssistanceRequests = (clone $assistanceQuery)
                ->with(['residentProfile.user', 'serviceType', 'assistanceDetail'])
                ->latest('submitted_at')
                ->limit(6)
                ->get();
        }

        return view('barangay.dashboard', [
            'user' => $user,
            'officialProfile' => $officialProfile,
            'barangay' => $barangay,
            'stats' => $stats,
            'recentVerifications' => $recentVerifications,
            'recentDocumentRequests' => $recentDocumentRequests,
            'recentAssistanceRequests' => $recentAssistanceRequests,
        ]);
    }
}
