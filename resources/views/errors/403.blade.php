<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-4xl items-center px-6 py-12">
        <div class="w-full rounded-3xl border border-slate-200 bg-white p-10 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">403</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Access Denied</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                This account does not have permission to open the requested module.
            </p>

            @auth
                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-sm font-semibold text-slate-900">Available destinations for your current account</p>

                    <div class="mt-4 flex flex-wrap gap-3">
                        @if (auth()->user()->isBarangayOfficial())
                            <a href="{{ route('barangay.dashboard') }}"
                               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                Dashboard
                            </a>

                            @if (auth()->user()->canAccessBarangayPermission('verification_review'))
                                <a href="{{ route('barangay.verifications.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Verifications
                                </a>
                            @endif

                            @if (auth()->user()->canAccessBarangayPermission('request_processing'))
                                <a href="{{ route('barangay.documents.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Documents
                                </a>

                                <a href="{{ route('barangay.assistance.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Assistance
                                </a>
                            @endif

                            @if (auth()->user()->canAccessBarangayPermission('payment_processing'))
                                <a href="{{ route('barangay.payments.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Payments
                                </a>
                            @endif

                            @if (auth()->user()->canAccessBarangayPermission('release_processing'))
                                <a href="{{ route('barangay.releases.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Releases
                                </a>
                            @endif

                            @if (auth()->user()->canAccessBarangayPermission('referral_processing'))
                                <a href="{{ route('barangay.referrals.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Referrals
                                </a>
                            @endif

                            @if (auth()->user()->canAccessBarangayPermission('reports'))
                                <a href="{{ route('barangay.reports.index') }}"
                                   class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                    Reports
                                </a>
                            @endif
                        @elseif (auth()->user()->isCitySuperAdmin())
                            <a href="{{ route('super_admin.dashboard') }}"
                               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                Super Admin Dashboard
                            </a>
                        @elseif (auth()->user()->isResident())
                            <a href="{{ route('resident.dashboard') }}"
                               class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-white">
                                Resident Dashboard
                            </a>
                        @endif
                    </div>
                </div>
            @endauth
        </div>
    </main>
</body>
</html>
