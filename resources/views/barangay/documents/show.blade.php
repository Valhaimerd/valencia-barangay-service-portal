@extends('layouts.barangay')

@section('content')
    @php
        $schema = \App\Support\ServiceRequestSchema::for($serviceRequest->serviceType);
        $summaryRows = \App\Support\ServiceRequestSchema::summaryRows($serviceRequest);
        $attachmentsByType = $serviceRequest->attachments->groupBy('attachment_type');
        $requiredAttachmentDefinitions = $schema['attachments'] ?? [];
        $otherAttachments = $attachmentsByType->get('supporting_document', collect());
        $isPrintable = in_array($serviceRequest->current_status, ['for_printing', 'ready_for_pickup', 'released'], true);
    @endphp

    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Document Request</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $serviceRequest->serviceType?->name }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $serviceRequest->reference_number }} · {{ $residentProfile->full_name }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$serviceRequest->current_status" />

                    @if ($isPrintable)
                        <a href="{{ route('barangay.documents.print', $serviceRequest) }}"
                           target="_blank"
                           class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Open Printable Copy
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Resident Summary</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Resident</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->full_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Email</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->user?->email }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Barangay</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->barangay?->name }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-slate-500">Current Address</span>
                            <span class="max-w-[70%] text-right font-medium text-slate-900">{{ $residentProfile->current_address_line }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Service-Specific Details</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        @foreach ($summaryRows as $row)
                            <div class="flex items-start justify-between gap-4">
                                <span class="text-slate-500">{{ $row['label'] }}</span>
                                <span class="max-w-[70%] text-right font-medium text-slate-900">{{ $row['value'] }}</span>
                            </div>
                        @endforeach

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Document Number</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->generatedDocument?->document_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Payment Amount</span>
                            <span class="font-medium text-slate-900">
                                {{ $documentDetail?->payment_amount !== null ? number_format($documentDetail->payment_amount, 2) : '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Official Receipt</span>
                            <span class="font-medium text-slate-900">{{ $documentDetail?->official_receipt_number ?: '—' }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Required Attachments</h2>

                    <div class="mt-6 space-y-3">
                        @foreach ($requiredAttachmentDefinitions as $attachmentType => $attachmentDefinition)
                            @php
                                $uploadedAttachment = $attachmentsByType->get($attachmentType, collect())->first();
                            @endphp

                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ \App\Support\ServiceRequestSchema::attachmentLabel($serviceRequest->serviceType, $attachmentType) }}</p>
                                    <p class="text-xs text-slate-500">{{ $uploadedAttachment?->original_name ?? 'No uploaded file found' }}</p>
                                </div>

                                <x-status-badge :status="$uploadedAttachment?->review_status ?? 'rejected'" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 border-t border-slate-200 pt-8">
                        <h3 class="text-lg font-semibold text-slate-900">Other Supporting Files</h3>

                        <div class="mt-4 space-y-3">
                            @forelse ($otherAttachments as $attachment)
                                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ \App\Support\ServiceRequestSchema::attachmentLabel($serviceRequest->serviceType, $attachment->attachment_type) }}</p>
                                        <p class="text-xs text-slate-500">{{ $attachment->original_name ?? basename($attachment->file_path) }}</p>
                                    </div>

                                    <x-status-badge :status="$attachment->review_status" />
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                    No extra supporting files uploaded.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                @if (! empty($schema['review_checklist']))
                    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Review Checklist</h2>

                        <div class="mt-6 space-y-3">
                            @foreach ($schema['review_checklist'] as $checkItem)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    {{ $checkItem }}
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Status Timeline</h2>
                            <p class="mt-2 text-sm text-slate-500">Operational status history.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-request-timeline :logs="$serviceRequest->statusLogs" :currentStatus="$serviceRequest->current_status" />
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Operational Records</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Generated Document No.</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->generatedDocument?->document_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Generated At</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->generatedDocument?->generated_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Printed At</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->generatedDocument?->printed_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Released To</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_to_name ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Released At</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>
                    </div>

                    @if ($isPrintable)
                        <div class="mt-8">
                            <a href="{{ route('barangay.documents.print', $serviceRequest) }}"
                               target="_blank"
                               class="inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                                Open Printable Copy
                            </a>
                        </div>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Update Workflow</h2>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Please fix the highlighted fields.</h3>
                            <ul class="mt-3 space-y-1 text-sm text-rose-700">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('barangay.documents.update', $serviceRequest) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="next_status" class="text-sm font-medium text-slate-700">Next Status</label>
                            <select id="next_status"
                                    name="next_status"
                                    class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                <option value="">Select status</option>
                                @foreach (config('portal.document_request_statuses') as $value => $label)
                                    @if (! in_array($value, ['submitted'], true))
                                        <option value="{{ $value }}" @selected(old('next_status', $serviceRequest->current_status) === $value)>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="purpose" class="text-sm font-medium text-slate-700">Purpose</label>
                            <textarea id="purpose"
                                      name="purpose"
                                      rows="4"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('purpose', $documentDetail?->purpose) }}</textarea>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="cedula_number" class="text-sm font-medium text-slate-700">Cedula Number</label>
                                <input id="cedula_number"
                                       name="cedula_number"
                                       type="text"
                                       value="{{ old('cedula_number', $documentDetail?->cedula_number) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="cedula_date" class="text-sm font-medium text-slate-700">Cedula Date</label>
                                <input id="cedula_date"
                                       name="cedula_date"
                                       type="date"
                                       value="{{ old('cedula_date', optional($documentDetail?->cedula_date)->format('Y-m-d')) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="cedula_place" class="text-sm font-medium text-slate-700">Cedula Place</label>
                                <input id="cedula_place"
                                       name="cedula_place"
                                       type="text"
                                       value="{{ old('cedula_place', $documentDetail?->cedula_place) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="years_of_residency" class="text-sm font-medium text-slate-700">Years of Residency</label>
                                <input id="years_of_residency"
                                       name="years_of_residency"
                                       type="number"
                                       min="0"
                                       value="{{ old('years_of_residency', $documentDetail?->years_of_residency) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="months_of_residency" class="text-sm font-medium text-slate-700">Months of Residency</label>
                                <input id="months_of_residency"
                                       name="months_of_residency"
                                       type="number"
                                       min="0"
                                       max="11"
                                       value="{{ old('months_of_residency', $documentDetail?->months_of_residency) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                                    <input type="checkbox"
                                           name="oath_required"
                                           value="1"
                                           @checked(old('oath_required', $documentDetail?->oath_required))
                                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                    <span>Oath Required</span>
                                </label>
                            </div>
                        </div>

                        @if ($serviceRequest->serviceType?->requires_payment)
                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <label for="payment_amount" class="text-sm font-medium text-slate-700">Payment Amount</label>
                                    <input id="payment_amount"
                                           name="payment_amount"
                                           type="number"
                                           min="0"
                                           step="0.01"
                                           value="{{ old('payment_amount', $documentDetail?->payment_amount) }}"
                                           class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                </div>

                                <div>
                                    <label for="official_receipt_number" class="text-sm font-medium text-slate-700">Official Receipt Number</label>
                                    <input id="official_receipt_number"
                                           name="official_receipt_number"
                                           type="text"
                                           value="{{ old('official_receipt_number', $documentDetail?->official_receipt_number) }}"
                                           class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="released_to_name" class="text-sm font-medium text-slate-700">Released To Name</label>
                                <input id="released_to_name"
                                       name="released_to_name"
                                       type="text"
                                       value="{{ old('released_to_name', $serviceRequest->releaseRecord?->released_to_name) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="released_to_relationship" class="text-sm font-medium text-slate-700">Relationship</label>
                                <input id="released_to_relationship"
                                       name="released_to_relationship"
                                       type="text"
                                       value="{{ old('released_to_relationship', $serviceRequest->releaseRecord?->released_to_relationship) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div class="md:col-span-2">
                                <label for="claimant_identification_notes" class="text-sm font-medium text-slate-700">Claimant Identification Notes</label>
                                <textarea id="claimant_identification_notes"
                                          name="claimant_identification_notes"
                                          rows="3"
                                          class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('claimant_identification_notes', $serviceRequest->releaseRecord?->claimant_identification_notes) }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label for="remarks" class="text-sm font-medium text-slate-700">Workflow Remarks</label>
                            <textarea id="remarks"
                                      name="remarks"
                                      rows="4"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('remarks', $serviceRequest->internal_notes) }}</textarea>
                        </div>

                        <div>
                            <label for="rejection_reason" class="text-sm font-medium text-slate-700">Rejection Reason</label>
                            <textarea id="rejection_reason"
                                      name="rejection_reason"
                                      rows="4"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('rejection_reason', $serviceRequest->rejection_reason) }}</textarea>
                        </div>

                        <div>
                            <label for="cancellation_reason" class="text-sm font-medium text-slate-700">Cancellation Reason</label>
                            <textarea id="cancellation_reason"
                                      name="cancellation_reason"
                                      rows="4"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('cancellation_reason', $serviceRequest->cancellation_reason) }}</textarea>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <a href="{{ route('barangay.documents.index') }}"
                               class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Back to Document Queue
                            </a>

                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                                Save Document Workflow
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection
