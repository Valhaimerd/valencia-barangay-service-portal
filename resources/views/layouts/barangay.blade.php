<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barangay Portal - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    @php
        $currentUser = auth()->user();
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-800 bg-slate-900 text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('barangay.dashboard') }}" class="text-lg font-semibold tracking-tight">
                        Barangay Portal
                    </a>
                    <p class="text-xs text-slate-300">
                        Operational workspace for verification, requests, and release
                    </p>
                </div>

                <nav class="flex flex-wrap items-center gap-4 text-sm">
                    <a href="{{ route('barangay.dashboard') }}" class="text-slate-200 hover:text-white">Dashboard</a>

                    @if ($currentUser?->canAccessBarangayPermission('reports'))
                        <a href="{{ route('barangay.reports.index') }}" class="text-slate-200 hover:text-white">Reports</a>
                    @endif

                    @if ($currentUser?->canAccessBarangayPermission('verification_review'))
                        <a href="{{ route('barangay.verifications.index') }}" class="text-slate-200 hover:text-white">Verifications</a>
                    @endif

                    @if ($currentUser?->canAccessBarangayPermission('request_processing'))
                        <a href="{{ route('barangay.documents.index') }}" class="text-slate-200 hover:text-white">Documents</a>
                        <a href="{{ route('barangay.assistance.index') }}" class="text-slate-200 hover:text-white">Assistance</a>
                    @endif

                    @if ($currentUser?->canAccessBarangayPermission('payment_processing'))
                        <a href="{{ route('barangay.payments.index') }}" class="text-slate-200 hover:text-white">Payments</a>
                    @endif

                    @if ($currentUser?->canAccessBarangayPermission('release_processing'))
                        <a href="{{ route('barangay.releases.index') }}" class="text-slate-200 hover:text-white">Releases</a>
                    @endif

                    @if ($currentUser?->canAccessBarangayPermission('referral_processing'))
                        <a href="{{ route('barangay.referrals.index') }}" class="text-slate-200 hover:text-white">Referrals</a>
                    @endif

                    <a href="{{ route('home') }}" class="text-slate-200 hover:text-white">Public Home</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-700 px-4 py-2 text-slate-200 hover:border-slate-500 hover:text-white">
                            Log out
                        </button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-10">
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
