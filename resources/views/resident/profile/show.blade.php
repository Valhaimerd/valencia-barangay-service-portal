@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resident Profile</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $residentProfile?->full_name ?? $user->name }}
                    </h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Resident profile shell and verification overview.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Verification Status</p>
                    <div class="mt-2">
                        <x-status-badge :status="$verification?->status ?? 'pending_verification'" />
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Profile Details</h2>

                <div class="mt-6 grid gap-4 md:grid-cols-2 text-sm">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
                        <p class="mt-2 font-medium text-slate-900">{{ $user->email }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Barangay</p>
                        <p class="mt-2 font-medium text-slate-900">{{ $residentProfile?->barangay?->name ?? 'Not yet set' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Mobile Number</p>
                        <p class="mt-2 font-medium text-slate-900">{{ $residentProfile?->mobile_number ?? 'Not yet set' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Sex</p>
                        <p class="mt-2 font-medium text-slate-900">{{ $residentProfile?->sex ?? 'Not yet set' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Birth Date</p>
                        <p class="mt-2 font-medium text-slate-900">
                            {{ $residentProfile?->birth_date?->format('F d, Y') ?? 'Not yet set' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Civil Status</p>
                        <p class="mt-2 font-medium text-slate-900">{{ $residentProfile?->civil_status ?? 'Not yet set' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Current Address</p>
                        <p class="mt-2 font-medium text-slate-900">
                            {{ $residentProfile?->current_address_line ?? 'Not yet set' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Permanent Address</p>
                        <p class="mt-2 font-medium text-slate-900">
                            {{ $residentProfile?->permanent_address_line ?? 'Not yet set' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Verification Summary</h2>

                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Method</span>
                            <span class="font-medium text-slate-900">{{ $verification?->verification_method ?? 'Not yet set' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Submitted</span>
                            <span class="font-medium text-slate-900">
                                {{ $verification?->submitted_at?->format('M d, Y h:i A') ?? 'Not yet set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Reviewed</span>
                            <span class="font-medium text-slate-900">
                                {{ $verification?->reviewed_at?->format('M d, Y h:i A') ?? 'Not yet reviewed' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Approved</span>
                            <span class="font-medium text-slate-900">
                                {{ $verification?->approved_at?->format('M d, Y h:i A') ?? 'Not yet approved' }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Verification Files</h2>

                    <div class="mt-6 space-y-3">
                        @forelse ($verification?->files ?? [] as $file)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $file->file_type }}</p>
                                    <p class="text-xs text-slate-500">{{ $file->original_name ?? basename($file->file_path) }}</p>
                                </div>

                                <x-status-badge :status="$file->review_status" />
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                No verification files yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </section>
    </div>
@endsection
