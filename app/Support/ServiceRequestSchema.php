<?php

namespace App\Support;

use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceRequestSchema
{
    public static function all(): array
    {
        return config('service_rules.services', []);
    }

    public static function for(ServiceType|string $serviceType): array
    {
        $code = $serviceType instanceof ServiceType ? $serviceType->code : $serviceType;

        return self::all()[$code] ?? [
            'description' => null,
            'fields' => [],
            'attachments' => [],
            'review_checklist' => [],
            'document_defaults' => [],
            'assistance_defaults' => [],
            'constraints' => [],
        ];
    }

    public static function description(ServiceType|string $serviceType): ?string
    {
        return self::for($serviceType)['description'] ?? null;
    }

    public static function fields(ServiceType|string $serviceType): array
    {
        return self::for($serviceType)['fields'] ?? [];
    }

    public static function attachments(ServiceType|string $serviceType): array
    {
        return self::for($serviceType)['attachments'] ?? [];
    }

    public static function reviewChecklist(ServiceType|string $serviceType): array
    {
        return self::for($serviceType)['review_checklist'] ?? [];
    }

    public static function validationRules(ServiceType|string $serviceType): array
    {
        $rules = [];

        foreach (self::fields($serviceType) as $fieldKey => $field) {
            $rules[$fieldKey] = $field['rules'] ?? ['nullable', 'string'];
        }

        $rules['attachments'] = ['nullable', 'array'];

        foreach (self::attachments($serviceType) as $attachmentKey => $attachment) {
            $rules["attachments.{$attachmentKey}"] = $attachment['rules']
                ?? config('service_rules.default_attachment_rules');
        }

        $rules['other_supporting_files'] = ['nullable', 'array'];
        $rules['other_supporting_files.*'] = ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'];

        return $rules;
    }

    public static function assertBusinessRules(ServiceType $serviceType, int $residentProfileId): void
    {
        $schema = self::for($serviceType);
        $constraints = $schema['constraints'] ?? [];

        if (($constraints['single_active_request'] ?? false) === true) {
            $existingActive = ServiceRequest::query()
                ->where('resident_profile_id', $residentProfileId)
                ->whereHas('serviceType', fn ($query) => $query->where('code', $serviceType->code))
                ->whereNotIn('current_status', ['rejected', 'cancelled'])
                ->exists();

            if ($existingActive) {
                throw ValidationException::withMessages([
                    'service_type_code' => 'An active request for this service already exists for this resident.',
                ]);
            }
        }
    }

    public static function documentPayload(Request $request, ServiceType|string $serviceType): array
    {
        $schema = self::for($serviceType);

        return array_merge(
            self::payloadForTarget($request, $serviceType, 'document'),
            $schema['document_defaults'] ?? []
        );
    }

    public static function assistancePayload(Request $request, ServiceType|string $serviceType): array
    {
        $schema = self::for($serviceType);

        return array_merge(
            self::payloadForTarget($request, $serviceType, 'assistance'),
            $schema['assistance_defaults'] ?? []
        );
    }

    public static function summaryRows(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing('serviceType', 'documentDetail', 'assistanceDetail');

        $rows = [];
        $schema = self::for($serviceRequest->serviceType);
        $detail = $serviceRequest->request_category === 'document'
            ? $serviceRequest->documentDetail
            : $serviceRequest->assistanceDetail;

        if (! $detail) {
            return [];
        }

        foreach ($schema['fields'] ?? [] as $fieldKey => $field) {
            if (($field['target'] ?? null) !== $serviceRequest->request_category) {
                continue;
            }

            $rows[] = [
                'label' => $field['label'] ?? Str::of($fieldKey)->replace('_', ' ')->title()->toString(),
                'value' => self::formatValue(data_get($detail, $fieldKey), $field),
            ];
        }

        return $rows;
    }

    public static function attachmentLabel(ServiceType|string $serviceType, string $attachmentType): string
    {
        $schema = self::for($serviceType);

        if (isset($schema['attachments'][$attachmentType]['label'])) {
            return $schema['attachments'][$attachmentType]['label'];
        }

        return match ($attachmentType) {
            'supporting_document' => 'Other Supporting Document',
            default => Str::of($attachmentType)->replace('_', ' ')->title()->toString(),
        };
    }

    private static function payloadForTarget(Request $request, ServiceType|string $serviceType, string $target): array
    {
        $payload = [];

        foreach (self::fields($serviceType) as $fieldKey => $field) {
            if (($field['target'] ?? null) !== $target) {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $cast = $field['cast'] ?? null;

            if ($type === 'checkbox' || $cast === 'boolean') {
                $payload[$fieldKey] = $request->boolean($fieldKey);
                continue;
            }

            if (! $request->filled($fieldKey)) {
                $payload[$fieldKey] = null;
                continue;
            }

            $value = $request->input($fieldKey);

            if ($cast === 'integer') {
                $payload[$fieldKey] = (int) $value;
                continue;
            }

            if ($cast === 'decimal') {
                $payload[$fieldKey] = (float) $value;
                continue;
            }

            $payload[$fieldKey] = $value;
        }

        return $payload;
    }

    private static function formatValue(mixed $value, array $field): string
    {
        if (($field['type'] ?? null) === 'checkbox' || ($field['cast'] ?? null) === 'boolean') {
            return $value ? 'Yes' : 'No';
        }

        if (($field['type'] ?? null) === 'date') {
            return $value ? Carbon::parse($value)->format('F d, Y') : '—';
        }

        if (($field['cast'] ?? null) === 'decimal') {
            return $value !== null && $value !== ''
                ? number_format((float) $value, 2)
                : '—';
        }

        if (($field['cast'] ?? null) === 'integer') {
            return $value !== null && $value !== ''
                ? (string) ((int) $value)
                : '—';
        }

        return filled($value) ? (string) $value : '—';
    }
}
