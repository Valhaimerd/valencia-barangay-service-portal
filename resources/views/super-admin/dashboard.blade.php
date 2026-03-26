@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">City Super Admin</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        Governance Dashboard
                    </h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Official account creation and city-level oversight are now active.
                    </p>
                </div>

                <a href="{{ route('super_admin.officials.create') }}"
                   class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                    Create Official Account
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Officials</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['total_officials'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Active Officials</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['active_officials'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Residents</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['total_residents'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pending Verifications</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['pending_verifications'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Active Barangays</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['active_barangays'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Official Accounts</h2>
                        <p class="mt-2 text-sm text-slate-500">Latest barangay official assignments.</p>
                    </div>

                    <a href="{{ route('super_admin.officials.index') }}"
                       class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        View All
                    </a>
                </div>

                <div class="mt-6 space-y-3">
                    @forelse ($recentOfficials as $official)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $official->user?->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ config('portal.official_roles.' . $official->official_role, $official->official_role) }}
                                    ·
                                    {{ $official->barangay?->name ?? 'No barangay assigned' }}
                                </p>
                            </div>

                            <x-status-badge :status="$official->is_active ? 'active' : 'deactivated'" />
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                            No official accounts yet.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Quick Access</h2>

                <div class="mt-6 grid gap-4">
                    <a href="{{ route('super_admin.officials.create') }}"
                       class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Create Official Account</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Register a new barangay official with barangay and role assignment.</p>
                    </a>

                    <a href="{{ route('super_admin.officials.index') }}"
                       class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Manage Official Accounts</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Edit email, password, barangay assignment, role, and account status.</p>
                    </a>
                </div>
            </section>
        </section>
    </div>
@endsection
