<?php

namespace App\Http\Controllers\Barangay;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Support\GeneratedDocumentViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeneratedDocumentController extends Controller
{
    public function show(Request $request, ServiceRequest $serviceRequest): View
    {
        $user = $request->user()->load('officialProfile.barangay');

        $serviceRequest->load([
            'residentProfile.user',
            'residentProfile.barangay',
            'barangay',
            'serviceType',
            'documentDetail',
            'generatedDocument.preparedBy',
            'generatedDocument.printedBy',
            'releaseRecord.releasedBy',
        ]);

        $this->ensureSameBarangayDocument($user->officialProfile?->barangay_id, $serviceRequest);

        abort_unless(
            $serviceRequest->generatedDocument ||
            in_array($serviceRequest->current_status, ['for_printing', 'ready_for_pickup', 'released'], true),
            403,
            'This request is not yet in a printable document state.'
        );

        return view('barangay.documents.print', [
            'serviceRequest' => $serviceRequest,
            'documentData' => GeneratedDocumentViewData::build($serviceRequest),
        ]);
    }

    private function ensureSameBarangayDocument(?int $officialBarangayId, ServiceRequest $serviceRequest): void
    {
        abort_unless($officialBarangayId, 403, 'This official account has no barangay assignment.');

        abort_unless(
            $serviceRequest->barangay_id === $officialBarangayId &&
            $serviceRequest->request_category === 'document',
            404
        );
    }
}
