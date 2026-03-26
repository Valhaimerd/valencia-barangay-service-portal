@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-rose-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-600">Verification Status</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Registration Rejected</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Your resident registration was rejected and cannot access resident services yet.
                    </p>
                </div>

                <x-status-badge :status="$verification->status" />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-3xl border border-rose-200 bg-rose-50 p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-rose-700">Rejection Reason</h2>
                <p class="mt-4 text-sm leading-7 text-rose-700">
                    {{ $verification->rejection_reason ?: 'No rejection reason recorded.' }}
                </p>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Registration Summary</h2>

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
    </div>
@endsection
