@extends('layouts.barangay')

@section('content')
    @php
        $schema = \App\Support\ServiceRequestSchema::for($serviceRequest->serviceType);
        $summaryRows = \App\Support\ServiceRequestSchema::summaryRows($serviceRequest);
        $attachmentsByType = $serviceRequest->attachments->groupBy('attachment_type');
        $requiredAttachmentDefinitions = $schema['attachments'] ?? [];
        $otherAttachments = $attachmentsByType->get('supporting_document', collect());
    @endphp

    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Assistance Request</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $serviceRequest->serviceType?->name }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $serviceRequest->reference_number }} · {{ $residentProfile->full_name }}
                    </p>
                </div>

                <x-status-badge :status="$serviceRequest->current_status" />
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
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Referral and Release Records</h2>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Release Record</h3>
                            <div class="mt-4 grid gap-3 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-500">Released To</span>
                                    <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_to_name ?: '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-500">Relationship</span>
                                    <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_to_relationship ?: '—' }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-slate-500">Released At</span>
                                    <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_at?->format('F d, Y h:i A') ?: '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Referral History</h3>
                            <div class="mt-4 space-y-3">
                                @forelse ($serviceRequest->referralRecords as $referral)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <p class="text-sm font-semibold text-slate-900">{{ $referral->referred_to }}</p>
                                            <x-status-badge :status="$referral->referral_status" />
                                        </div>
                                        <p class="mt-2 text-sm text-slate-600">{{ $referral->referral_notes ?: 'No referral notes.' }}</p>
                                        <p class="mt-2 text-xs text-slate-500">{{ $referral->referred_at?->format('F d, Y h:i A') }}</p>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500">
                                        No referral records yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
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

                    <form method="POST" action="{{ route('barangay.assistance.update', $serviceRequest) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="next_status" class="text-sm font-medium text-slate-700">Next Status</label>
                            <select id="next_status"
                                    name="next_status"
                                    class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                <option value="">Select status</option>
                                @foreach (config('portal.assistance_request_statuses') as $value => $label)
                                    @if (! in_array($value, ['submitted'], true))
                                        <option value="{{ $value }}" @selected(old('next_status', $serviceRequest->current_status) === $value)>{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="case_summary" class="text-sm font-medium text-slate-700">Case Summary</label>
                            <textarea id="case_summary"
                                      name="case_summary"
                                      rows="5"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('case_summary', $assistanceDetail?->case_summary) }}</textarea>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="requested_amount" class="text-sm font-medium text-slate-700">Requested Amount</label>
                                <input id="requested_amount"
                                       name="requested_amount"
                                       type="number"
                                       min="0"
                                       step="0.01"
                                       value="{{ old('requested_amount', $assistanceDetail?->requested_amount) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="assessment_date" class="text-sm font-medium text-slate-700">Assessment Date</label>
                                <input id="assessment_date"
                                       name="assessment_date"
                                       type="date"
                                       value="{{ old('assessment_date', optional($assistanceDetail?->assessment_date)->format('Y-m-d')) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>
                        </div>

                        <div>
                            <label for="assessment_notes" class="text-sm font-medium text-slate-700">Assessment Notes</label>
                            <textarea id="assessment_notes"
                                      name="assessment_notes"
                                      rows="4"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('assessment_notes', $assistanceDetail?->assessment_notes) }}</textarea>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="claimant_name" class="text-sm font-medium text-slate-700">Claimant Name</label>
                                <input id="claimant_name"
                                       name="claimant_name"
                                       type="text"
                                       value="{{ old('claimant_name', $assistanceDetail?->claimant_name) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="relationship_to_beneficiary" class="text-sm font-medium text-slate-700">Relationship to Beneficiary</label>
                                <input id="relationship_to_beneficiary"
                                       name="relationship_to_beneficiary"
                                       type="text"
                                       value="{{ old('relationship_to_beneficiary', $assistanceDetail?->relationship_to_beneficiary) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="referred_to" class="text-sm font-medium text-slate-700">Referred To</label>
                                <input id="referred_to"
                                       name="referred_to"
                                       type="text"
                                       value="{{ old('referred_to') }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="referral_notes" class="text-sm font-medium text-slate-700">Referral Notes</label>
                                <input id="referral_notes"
                                       name="referral_notes"
                                       type="text"
                                       value="{{ old('referral_notes') }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>
                        </div>

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
                                <label for="released_to_relationship" class="text-sm font-medium text-slate-700">Released To Relationship</label>
                                <input id="released_to_relationship"
                                       name="released_to_relationship"
                                       type="text"
                                       value="{{ old('released_to_relationship', $serviceRequest->releaseRecord?->released_to_relationship) }}"
                                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </div>
                        </div>

                        <div>
                            <label for="claimant_identification_notes" class="text-sm font-medium text-slate-700">Claimant Identification Notes</label>
                            <textarea id="claimant_identification_notes"
                                      name="claimant_identification_notes"
                                      rows="3"
                                      class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('claimant_identification_notes', $serviceRequest->releaseRecord?->claimant_identification_notes) }}</textarea>
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
                            <a href="{{ route('barangay.assistance.index') }}"
                               class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Back to Assistance Queue
                            </a>

                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                                Save Assistance Workflow
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection
