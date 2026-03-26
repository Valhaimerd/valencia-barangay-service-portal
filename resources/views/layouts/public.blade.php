<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    @php
        $currentUser = auth()->user();
        $dashboardRoute = null;

        if ($currentUser?->isCitySuperAdmin()) {
            $dashboardRoute = route('super_admin.dashboard');
        } elseif ($currentUser?->isBarangayOfficial()) {
            $dashboardRoute = route($currentUser->preferredBarangayRoute());
        } elseif ($currentUser?->isResident()) {
            $dashboardRoute = route('resident.dashboard');
        }
    @endphp

    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-slate-950">
                        {{ config('app.name') }}
                    </a>
                    <p class="text-xs text-slate-500">
                        Digital Service Access and Request Tracking System
                    </p>
                </div>

                <nav class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Home</a>
                    <a href="{{ route('services.index') }}" class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Services</a>

                    @auth
                        @if ($dashboardRoute)
                            <a href="{{ $dashboardRoute }}"
                               class="rounded-lg bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-800">
                                Open Portal
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">
                                Log out
                            </button>
                        </form>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">
                                Resident Sign Up
                            </a>
                        @endif

                        <a href="{{ route('login') }}"
                           class="rounded-lg bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-800">
                            Login
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-10">
            @yield('content')
        </main>
    </div>
</body>
</html>
