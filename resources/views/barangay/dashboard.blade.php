@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        @php
            $officialRoleLabel = config('portal.official_roles.' . ($officialProfile?->official_role ?? ''), $officialProfile?->official_role ?? 'Unassigned');
        @endphp

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Barangay Portal</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $barangay?->name ?? 'Unassigned Barangay' }} Dashboard
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Access is now limited by official role so each staff member sees only the correct operational space.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Official Role</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $officialRoleLabel }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Access Scope</h2>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @if ($user->canAccessBarangayPermission('verification_review'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Verification Review</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Resident onboarding review, correction, rejection, and approval.</p>
                    </div>
                @endif

                @if ($user->canAccessBarangayPermission('request_processing'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Request Processing</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Document and assistance workflow handling.</p>
                    </div>
                @endif

                @if ($user->canAccessBarangayPermission('payment_processing'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Payment Processing</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Official receipt recording and payment confirmation.</p>
                    </div>
                @endif

                @if ($user->canAccessBarangayPermission('release_processing'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Release Processing</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Claim and release recording for documents and assistance.</p>
                    </div>
                @endif

                @if ($user->canAccessBarangayPermission('referral_processing'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Referral Processing</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">External referral routing and outcome closure.</p>
                    </div>
                @endif

                @if ($user->canAccessBarangayPermission('reports'))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-lg font-semibold text-slate-900">Reports</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Barangay-wide operational summaries and counts.</p>
                    </div>
                @endif
            </div>
        </section>

        @if ($user->canAccessBarangayPermission('verification_review'))
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Pending Verifications</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['pending_verifications'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Needs Correction</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['needs_correction'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Verified Residents</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['verified_residents'] }}</p>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Resident Verifications</h2>
                            <p class="mt-2 text-sm text-slate-500">Latest onboarding submissions for this barangay.</p>
                        </div>

                        <a href="{{ route('barangay.verifications.index') }}"
                           class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Open Verification Queue
                        </a>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse ($recentVerifications as $verification)
                            <a href="{{ route('barangay.verifications.show', $verification) }}"
                               class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 hover:bg-white">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $verification->residentProfile?->full_name ?? $verification->residentProfile?->user?->name }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $verification->submitted_at?->format('F d, Y h:i A') ?? 'No submission time' }}
                                    </p>
                                </div>

                                <x-status-badge :status="$verification->status" />
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                No resident verifications yet.
                            </div>
                        @endforelse
                    </div>
                </section>
            </section>
        @endif

        @if ($user->canAccessBarangayPermission('request_processing'))
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Document Requests</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['document_requests'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Assistance Requests</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['assistance_requests'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">For Assessment</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['for_assessment_assistance'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Referred Assistance</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['referred_assistance'] }}</p>
                    </div>
                </div>

                <section class="grid gap-6 xl:grid-cols-2">
                    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Document Requests</h2>
                                <p class="mt-2 text-sm text-slate-500">Latest certificate-related submissions.</p>
                            </div>

                            <a href="{{ route('barangay.documents.index') }}"
                               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Documents
                            </a>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($recentDocumentRequests as $documentRequest)
                                <a href="{{ route('barangay.documents.show', $documentRequest) }}"
                                   class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 hover:bg-white">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $documentRequest->serviceType?->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $documentRequest->reference_number }} · {{ $documentRequest->residentProfile?->full_name ?? $documentRequest->residentProfile?->user?->name }}
                                        </p>
                                    </div>

                                    <x-status-badge :status="$documentRequest->current_status" />
                                </a>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                    No document requests yet.
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Assistance Requests</h2>
                                <p class="mt-2 text-sm text-slate-500">Latest medical and educational assistance submissions.</p>
                            </div>

                            <a href="{{ route('barangay.assistance.index') }}"
                               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Assistance
                            </a>
                        </div>

                        <div class="mt-6 space-y-3">
                            @forelse ($recentAssistanceRequests as $assistanceRequest)
                                <a href="{{ route('barangay.assistance.show', $assistanceRequest) }}"
                                   class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 hover:bg-white">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $assistanceRequest->serviceType?->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $assistanceRequest->reference_number }} · {{ $assistanceRequest->residentProfile?->full_name ?? $assistanceRequest->residentProfile?->user?->name }}
                                        </p>
                                    </div>

                                    <x-status-badge :status="$assistanceRequest->current_status" />
                                </a>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                    No assistance requests yet.
                                </div>
                            @endforelse
                        </div>
                    </section>
                </section>
            </section>
        @endif

        @if ($user->canAccessBarangayPermission('payment_processing'))
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">For Payment</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['for_payment_documents'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Document Requests</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['document_requests'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Ready for Pickup</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['ready_for_pickup_documents'] }}</p>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Payment Operations</h2>
                            <p class="mt-2 text-sm text-slate-500">Record official receipts and move requests to printing.</p>
                        </div>

                        <a href="{{ route('barangay.payments.index') }}"
                           class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Open Payment Queue
                        </a>
                    </div>
                </section>
            </section>
        @endif

        @if ($user->canAccessBarangayPermission('release_processing'))
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Ready for Pickup</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['ready_for_pickup_documents'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Ready for Claim</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['ready_for_claim_assistance'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Total Releasable</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['releasable_total'] }}</p>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Release Operations</h2>
                            <p class="mt-2 text-sm text-slate-500">Record claimants and finalize request release.</p>
                        </div>

                        <a href="{{ route('barangay.releases.index') }}"
                           class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Open Release Queue
                        </a>
                    </div>
                </section>
            </section>
        @endif

        @if ($user->canAccessBarangayPermission('referral_processing'))
            <section class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Referred Assistance</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['referred_assistance'] }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">Assistance Requests</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['assistance_requests'] }}</p>
                    </div>
                </div>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Referral Operations</h2>
                            <p class="mt-2 text-sm text-slate-500">Track referral destinations and record final outcomes.</p>
                        </div>

                        <a href="{{ route('barangay.referrals.index') }}"
                           class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Open Referral Queue
                        </a>
                    </div>
                </section>
            </section>
        @endif

        @if ($user->canAccessBarangayPermission('reports'))
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Barangay Reports</h2>
                        <p class="mt-2 text-sm text-slate-500">City-facing summary and operational status counts.</p>
                    </div>

                    <a href="{{ route('barangay.reports.index') }}"
                       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Open Reports
                    </a>
                </div>
            </section>
        @endif
    </div>
@endsection
