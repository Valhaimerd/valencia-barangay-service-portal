@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-orange-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Verification Status</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Correction Required</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Your resident registration needs corrections before it can be approved.
                    </p>
                </div>

                <x-status-badge :status="$verification->status" />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <section class="space-y-6">
                <section class="rounded-3xl border border-orange-200 bg-orange-50 p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-orange-700">Correction Notes</h2>
                    <p class="mt-4 text-sm leading-7 text-orange-700">
                        {{ $verification->correction_notes ?: 'No correction notes recorded.' }}
                    </p>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Resident Summary</h2>

                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Resident</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->full_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Barangay</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->barangay?->name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Reviewed At</span>
                            <span class="font-medium text-slate-900">{{ $verification->reviewed_at?->format('F d, Y h:i A') ?? '—' }}</span>
                        </div>
                    </div>
                </section>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Submitted Files</h2>
                        <p class="mt-2 text-sm text-slate-500">Review the current uploaded files before editing.</p>
                    </div>

                    <a href="{{ route('resident.onboarding.edit') }}"
                       class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Edit Registration
                    </a>
                </div>

                <div class="mt-6 space-y-3">
                    @foreach ($files as $file)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ config('portal.verification_file_types.' . $file->file_type, $file->file_type) }}</p>
                                <p class="text-xs text-slate-500">{{ $file->original_name ?? basename($file->file_path) }}</p>
                            </div>

                            <x-status-badge :status="$file->review_status" />
                        </div>
                    @endforeach
                </div>
            </section>
        </section>
    </div>
@endsection
