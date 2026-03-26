<?php

namespace App\Support;

use App\Models\GeneratedDocument;
use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class DocumentNumber
{
    public static function generate(ServiceRequest $serviceRequest): string
    {
        $serviceRequest->loadMissing('serviceType');

        do {
            $serviceCode = collect(explode('_', $serviceRequest->serviceType->code))
                ->map(fn (string $segment) => strtoupper(Str::substr($segment, 0, 1)))
                ->implode('');

            $documentNumber = sprintf(
                'DOC-%s-B%02d-%s-%s',
                now()->format('Ymd'),
                $serviceRequest->barangay_id,
                $serviceCode,
                strtoupper(Str::random(6))
            );
        } while (
            GeneratedDocument::query()
                ->where('document_number', $documentNumber)
                ->exists()
        );

        return $documentNumber;
    }
}
