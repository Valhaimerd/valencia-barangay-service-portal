@php use Illuminate\Support\Facades\Storage; @endphp

@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resident Verification</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $residentProfile->full_name }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $residentProfile->barangay?->name }} · {{ $residentProfile->user?->email }}
                    </p>
                </div>

                <x-status-badge :status="$residentVerification->status" />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Resident Details</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Full Name</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->full_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Sex</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->sex }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Birth Date</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->birth_date?->format('F d, Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Mobile Number</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->mobile_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-slate-500">Current Address</span>
                            <span class="max-w-[70%] text-right font-medium text-slate-900">{{ $residentProfile->current_address_line }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-slate-500">Permanent Address</span>
                            <span class="max-w-[70%] text-right font-medium text-slate-900">{{ $residentProfile->permanent_address_line ?: '—' }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Verification Summary</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Method</span>
                            <span class="font-medium text-slate-900">
                                {{ config('portal.verification_methods.' . $residentVerification->verification_method, $residentVerification->verification_method) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Identity Document Type</span>
                            <span class="font-medium text-slate-900">{{ $residentVerification->identity_document_label ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Identity Document Number</span>
                            <span class="font-medium text-slate-900">{{ $residentVerification->identity_document_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Proof of Residency</span>
                            <span class="font-medium text-slate-900">{{ $residentVerification->proof_of_residency_label ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Submitted At</span>
                            <span class="font-medium text-slate-900">{{ $residentVerification->submitted_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Reviewed At</span>
                            <span class="font-medium text-slate-900">{{ $residentVerification->reviewed_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Reviewed By</span>
                            <span class="font-medium text-slate-900">{{ $reviewer?->name ?: '—' }}</span>
                        </div>
                    </div>

                    @if ($residentVerification->correction_notes)
                        <div class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-orange-700">Correction Notes</h3>
                            <p class="mt-3 text-sm leading-6 text-orange-700">{{ $residentVerification->correction_notes }}</p>
                        </div>
                    @endif

                    @if ($residentVerification->rejection_reason)
                        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Rejection Reason</h3>
                            <p class="mt-3 text-sm leading-6 text-rose-700">{{ $residentVerification->rejection_reason }}</p>
                        </div>
                    @endif
                </section>
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Verification Files</h2>

                    <form method="POST" action="{{ route('barangay.verifications.update', $residentVerification) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Please fix the highlighted fields.</h3>
                                <ul class="mt-3 space-y-1 text-sm text-rose-700">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-4">
                            @foreach ($residentVerification->files as $file)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <h3 class="text-sm font-semibold text-slate-900">
                                                {{ config('portal.verification_file_types.' . $file->file_type, $file->file_type) }}
                                            </h3>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $file->original_name ?? basename($file->file_path) }}
                                            </p>

                                            <a href="{{ Storage::disk('public')->url($file->file_path) }}"
                                               target="_blank"
                                               class="mt-2 inline-flex text-sm font-medium text-slate-700 hover:text-slate-900">
                                                Open File
                                            </a>
                                        </div>

                                        <x-status-badge :status="$file->review_status" />
                                    </div>

                                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="text-sm font-medium text-slate-700">File Review Status</label>
                                            <select name="file_statuses[{{ $file->id }}]"
                                                    class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                                @foreach (config('portal.file_review_statuses') as $value => $label)
                                                    <option value="{{ $value }}"
                                                        @selected(old("file_statuses.{$file->id}", $file->review_status) === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-slate-700">Reviewer Notes</label>
                                            <input type="text"
                                                   name="file_notes[{{ $file->id }}]"
                                                   value="{{ old("file_notes.{$file->id}", $file->reviewer_notes) }}"
                                                   class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-6">
                            <h3 class="text-lg font-semibold text-slate-900">Decision</h3>

                            <div class="mt-5 grid gap-5">
                                <div>
                                    <label for="decision" class="text-sm font-medium text-slate-700">Verification Decision</label>
                                    <select id="decision"
                                            name="decision"
                                            class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                        <option value="">Select decision</option>
                                        <option value="verified" @selected(old('decision') === 'verified')>Verify Resident</option>
                                        <option value="needs_correction" @selected(old('decision') === 'needs_correction')>Needs Correction</option>
                                        <option value="rejected" @selected(old('decision') === 'rejected')>Reject Registration</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="correction_notes" class="text-sm font-medium text-slate-700">Correction Notes</label>
                                    <textarea id="correction_notes"
                                              name="correction_notes"
                                              rows="4"
                                              class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('correction_notes', $residentVerification->correction_notes) }}</textarea>
                                </div>

                                <div>
                                    <label for="rejection_reason" class="text-sm font-medium text-slate-700">Rejection Reason</label>
                                    <textarea id="rejection_reason"
                                              name="rejection_reason"
                                              rows="4"
                                              class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('rejection_reason', $residentVerification->rejection_reason) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <a href="{{ route('barangay.verifications.index') }}"
                               class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Back to Verification Queue
                            </a>

                            <button type="submit"
                                    class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                                Save Verification Decision
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection
