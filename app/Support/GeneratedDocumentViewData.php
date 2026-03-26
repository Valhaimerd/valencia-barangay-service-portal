<?php

namespace App\Support;

use App\Models\ServiceRequest;
use Carbon\Carbon;

class GeneratedDocumentViewData
{
    public static function build(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing([
            'residentProfile.barangay',
            'serviceType',
            'documentDetail',
            'generatedDocument.preparedBy',
            'generatedDocument.printedBy',
        ]);

        $resident = $serviceRequest->residentProfile;
        $detail = $serviceRequest->documentDetail;
        $generated = $serviceRequest->generatedDocument;
        $serviceCode = $serviceRequest->serviceType?->code;
        $residentName = $resident?->full_name ?? $resident?->user?->name ?? '—';
        $barangayName = $resident?->barangay?->name ?? $serviceRequest->barangay?->name ?? '—';
        $issueDate = $generated?->generated_at?->format('F d, Y') ?? now()->format('F d, Y');
        $purpose = $detail?->purpose ?: 'official request purposes';
        $cedulaText = self::buildCedulaText($detail?->cedula_number, $detail?->cedula_date, $detail?->cedula_place);
        $residencyDuration = self::buildResidencyDuration($detail?->years_of_residency, $detail?->months_of_residency);

        [$documentTitle, $paragraphs, $footerNote] = match ($serviceCode) {
            'barangay_clearance' => [
                'BARANGAY CLEARANCE',
                array_filter([
                    "TO WHOM IT MAY CONCERN:",
                    "This is to certify that {$residentName} is a bona fide resident of Barangay {$barangayName}, Valencia City, Bukidnon.",
                    "This clearance is issued upon the request of the above-named resident for {$purpose}.",
                    $cedulaText,
                ]),
                'Not valid without document number, barangay release validation, and authorized barangay processing record.',
            ],
            'certificate_of_residency' => [
                'CERTIFICATE OF RESIDENCY',
                array_filter([
                    "TO WHOM IT MAY CONCERN:",
                    "This is to certify that {$residentName} is a resident of Barangay {$barangayName}, Valencia City, Bukidnon.",
                    $residencyDuration !== 'Not specified'
                        ? "The declared residency duration for this request is {$residencyDuration}."
                        : null,
                    "This certification is issued upon the request of the above-named resident for {$purpose}.",
                ]),
                'Issued based on barangay residency records and request details currently reflected in the portal.',
            ],
            'certificate_of_indigency' => [
                'CERTIFICATE OF INDIGENCY',
                array_filter([
                    "TO WHOM IT MAY CONCERN:",
                    "This is to certify that {$residentName} is a resident of Barangay {$barangayName}, Valencia City, Bukidnon.",
                    "This certification of indigency is issued upon request for {$purpose}.",
                    "This document is intended for barangay-supported certification processing subject to office review and issuance controls.",
                ]),
                'This certification is subject to barangay review, release controls, and document registry validation.',
            ],
            'first_time_jobseeker_certification' => [
                'FIRST-TIME JOBSEEKER CERTIFICATION',
                array_filter([
                    "TO WHOM IT MAY CONCERN:",
                    "This is to certify that {$residentName} is a bona fide resident of Barangay {$barangayName}, Valencia City, Bukidnon.",
                    "This certification is issued in support of a first-time job application request for {$purpose}.",
                    $detail?->oath_required
                        ? 'This request includes oath-related processing as part of first-time jobseeker certification handling.'
                        : null,
                ]),
                'For first-time jobseeker processing use only, subject to barangay release and registry validation.',
            ],
            default => [
                strtoupper($serviceRequest->serviceType?->name ?? 'DOCUMENT'),
                array_filter([
                    "TO WHOM IT MAY CONCERN:",
                    "This document is issued for {$residentName}, a resident of Barangay {$barangayName}, Valencia City, Bukidnon.",
                    "Purpose: {$purpose}.",
                ]),
                'Generated through barangay document workflow.',
            ],
        };

        return [
            'document_title' => $documentTitle,
            'document_number' => $generated?->document_number ?? 'Pending Document Number',
            'issue_date' => $issueDate,
            'resident_name' => $residentName,
            'barangay_name' => $barangayName,
            'city_name' => 'Valencia City',
            'province_name' => 'Bukidnon',
            'purpose' => $purpose,
            'cedula_number' => $detail?->cedula_number,
            'cedula_date' => $detail?->cedula_date ? Carbon::parse($detail->cedula_date)->format('F d, Y') : null,
            'cedula_place' => $detail?->cedula_place,
            'residency_duration' => $residencyDuration,
            'prepared_by' => $generated?->preparedBy?->name ?? 'Barangay Staff',
            'printed_by' => $generated?->printedBy?->name ?? 'Barangay Staff',
            'printed_at' => $generated?->printed_at?->format('F d, Y h:i A'),
            'paragraphs' => $paragraphs,
            'footer_note' => $footerNote,
        ];
    }

    private static function buildCedulaText(?string $cedulaNumber, mixed $cedulaDate, ?string $cedulaPlace): ?string
    {
        if (! $cedulaNumber && ! $cedulaDate && ! $cedulaPlace) {
            return null;
        }

        $formattedDate = $cedulaDate ? Carbon::parse($cedulaDate)->format('F d, Y') : 'an unspecified date';
        $formattedPlace = $cedulaPlace ?: 'an unspecified place';

        return "Cedula details recorded for this request: Cedula No. {$cedulaNumber}, issued on {$formattedDate} at {$formattedPlace}.";
    }

    private static function buildResidencyDuration(?int $years, ?int $months): string
    {
        $parts = [];

        if ($years !== null) {
            $parts[] = $years . ' year' . ($years === 1 ? '' : 's');
        }

        if ($months !== null) {
            $parts[] = $months . ' month' . ($months === 1 ? '' : 's');
        }

        return empty($parts) ? 'Not specified' : implode(' and ', $parts);
    }
}
