@extends('layouts.public')

@section('content')
    <div class="space-y-8">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-8 px-8 py-10 lg:grid-cols-[1.3fr_0.7fr] lg:px-10 lg:py-12">
                <div class="space-y-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Part 2 Active
                    </p>

                    <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-slate-950">
                        Valencia Multi-Barangay Service Portal
                    </h1>

                    <p class="max-w-3xl text-base leading-7 text-slate-600">
                        Public landing, service overview, resident shell pages, request history shell,
                        profile shell, notifications shell, and reusable status components are now active.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('services.index') }}" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Browse Services
                        </a>

                        @auth
                            <a href="{{ route('resident.dashboard') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Open Resident Portal
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                Create Resident Account
                            </a>
                        @endauth
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Pilot Barangays</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ count(config('portal.pilot_barangays')) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Supported Services</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">
                            {{ count(config('portal.supported_services')) }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-medium text-slate-500">Portal Spaces</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">3</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Resident Access</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Resident pages now include dashboard, profile shell, notification shell,
                    and request history shell.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Document Services</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Clearance, Residency, Indigency, and First-Time Jobseeker are already
                    represented in the shared service catalog.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Assistance Services</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Medical and Educational Assistance remain aligned and ready for later
                    workflow slices.
                </p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Pilot Barangays</h2>
                    <p class="mt-2 text-sm text-slate-500">Locked coverage values for the current project scope.</p>
                </div>

                <a href="{{ route('services.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    View Services
                </a>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (config('portal.pilot_barangays') as $barangay)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700">
                        {{ $barangay }}
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
