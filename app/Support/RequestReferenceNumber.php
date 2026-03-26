<?php

namespace App\Support;

use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Illuminate\Support\Str;

class RequestReferenceNumber
{
    public static function generate(ServiceType $serviceType, int $barangayId): string
    {
        do {
            $serviceCode = collect(explode('_', $serviceType->code))
                ->map(fn (string $segment) => strtoupper(Str::substr($segment, 0, 1)))
                ->implode('');

            $reference = sprintf(
                'REQ-%s-B%02d-%s-%s',
                now()->format('Ymd'),
                $barangayId,
                $serviceCode,
                strtoupper(Str::random(6))
            );
        } while (
            ServiceRequest::query()->where('reference_number', $reference)->exists()
        );

        return $reference;
    }
}
