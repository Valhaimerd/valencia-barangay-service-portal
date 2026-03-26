@extends('layouts.public')

@section('content')
    <div class="space-y-10">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Digital Service Access and Request Tracking System
                    </p>

                    <h1 class="mt-4 text-4xl font-bold tracking-tight text-slate-950 lg:text-5xl">
                        One public entry point for residents, barangay officials, and city administration.
                    </h1>

                    <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">
                        Submit barangay document requests, request assistance, track operational status, and manage verification workflows through a shared but role-aware portal.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login', ['portal' => 'resident']) }}"
                           class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Resident Login
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Resident Sign Up
                            </a>
                        @endif

                        <a href="{{ route('services.index') }}"
                           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Browse Services
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                    <div class="darts-brand-lockup">
                        <img src="{{ asset('branding/arrow.png') }}" alt="DARTS team icon" class="darts-brand-symbol">
                        <img src="{{ asset('branding/dart.png') }}" alt="DARTS wordmark" class="darts-brand-wordmark">
                        <p class="darts-brand-tagline">Digital Service Access and Request Tracking</p>
                    </div>

                    <h2 class="mt-6 text-xl font-semibold text-slate-900">Shared secure login</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        All portal roles use one secure login page. The system redirects each account to the correct workspace after authentication.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Resident</p>
                            <p class="mt-1 text-xs text-slate-500">Self-register, complete onboarding, request services, and track updates.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Barangay Official</p>
                            <p class="mt-1 text-xs text-slate-500">Access is role-limited by verifier, encoder, cashier, release officer, or barangay admin.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">City Super Admin</p>
                            <p class="mt-1 text-xs text-slate-500">Creates official accounts and reviews city-level reports and audit logs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Resident Portal</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Citizen entry point</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    For self-registration, resident verification, document requests, assistance requests, notifications, and tracking.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('login', ['portal' => 'resident']) }}"
                       class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Login
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Sign Up
                        </a>
                    @endif
                </div>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Barangay Official Portal</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Operational workspace</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    For verification review, request processing, payments, releases, referrals, and barangay reports based on assigned official role.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('login', ['portal' => 'barangay']) }}"
                       class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Official Login
                    </a>
                </div>

                <p class="mt-4 text-xs leading-6 text-slate-500">
                    Barangay official accounts are created by city-level administration, not through public self-registration.
                </p>
            </article>

            <article class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">City Super Admin Portal</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Governance workspace</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    For official account provisioning, audit trail review, city-wide reporting, and administrative oversight.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('login', ['portal' => 'super_admin']) }}"
                       class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Admin Login
                    </a>
                </div>

                <p class="mt-4 text-xs leading-6 text-slate-500">
                    City super admin access is restricted and not publicly self-registered.
                </p>
            </article>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Featured Services</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Available resident-facing services</h2>
                </div>

                <a href="{{ route('services.index') }}"
                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    View Full Service Catalog
                </a>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($featuredServices as $service)
                    @php
                        $schema = \App\Support\ServiceRequestSchema::for($service);
                        $requiredAttachmentCount = collect($schema['attachments'] ?? [])->where('required', true)->count();
                    @endphp

                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ $service->name }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $schema['description'] ?: ucfirst($service->category) }}
                                </p>
                            </div>

                            <x-status-badge :status="$service->requires_payment ? 'for_payment' : 'active'" />
                        </div>

                        <div class="mt-5 space-y-2 text-xs text-slate-500">
                            <p>Category: {{ ucfirst($service->category) }}</p>
                            <p>Required attachments: {{ $requiredAttachmentCount }}</p>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                        No services available yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Step 1</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Account access</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Residents create accounts through public sign-up. Barangay officials and city administrators receive issued accounts.
                </p>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Step 2</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Verification and submission</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Residents complete onboarding, submit required files, and request certificates or assistance through service-specific forms.
                </p>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Step 3</p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950">Role-based processing</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Officials process requests by function while residents receive notifications and status updates through the portal.
                </p>
            </section>
        </section>
    </div>
@endsection
