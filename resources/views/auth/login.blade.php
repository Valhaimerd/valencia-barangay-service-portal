<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    @php
        $portal = request('portal');

        $portalMeta = match ($portal) {
            'resident' => [
                'label' => 'Resident Portal Login',
                'message' => 'Use this login for resident request tracking, onboarding, and notifications.',
            ],
            'barangay' => [
                'label' => 'Barangay Official Login',
                'message' => 'Use this login for barangay operational modules assigned to your official role.',
            ],
            'super_admin' => [
                'label' => 'City Super Admin Login',
                'message' => 'Use this login for city-level governance, reporting, and official account management.',
            ],
            default => [
                'label' => 'Shared Portal Login',
                'message' => 'All account roles sign in here and are redirected to the correct workspace after authentication.',
            ],
        };
    @endphp

    <main class="mx-auto flex min-h-screen max-w-7xl items-center px-6 py-12">
        <div class="grid w-full gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Authentication</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ $portalMeta['label'] }}</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $portalMeta['message'] }}</p>

                @if (session('status'))
                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Please fix the highlighted fields.</h2>
                        <ul class="mt-3 space-y-1 text-sm text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="text-sm font-medium text-slate-700">Email Address</label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-4">
                            <label for="password" class="text-sm font-medium text-slate-700">Password</label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-sm font-medium text-slate-600 hover:text-slate-900">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <input id="password"
                               name="password"
                               type="password"
                               required
                               autocomplete="current-password"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                        <input type="checkbox"
                               name="remember"
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>Remember me</span>
                    </label>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('home') }}"
                           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Back to Home
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Log In
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Choose your portal</h2>

                    <div class="mt-6 grid gap-4">
                        <a href="{{ route('login', ['portal' => 'resident']) }}"
                           class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:bg-white">
                            <h3 class="text-lg font-semibold text-slate-900">Resident</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">For onboarding, requests, tracking, and notifications.</p>
                        </a>

                        <a href="{{ route('login', ['portal' => 'barangay']) }}"
                           class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:bg-white">
                            <h3 class="text-lg font-semibold text-slate-900">Barangay Official</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">For verifier, encoder, cashier, release, and admin workspaces.</p>
                        </a>

                        <a href="{{ route('login', ['portal' => 'super_admin']) }}"
                           class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:bg-white">
                            <h3 class="text-lg font-semibold text-slate-900">City Super Admin</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">For system governance, reports, and audit review.</p>
                        </a>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Account creation rules</h2>

                    <div class="mt-6 space-y-4 text-sm text-slate-600">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">Residents</p>
                            <p class="mt-2 leading-6">Residents can create accounts publicly, then continue to onboarding and verification.</p>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                                    Resident Sign Up
                                </a>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">Barangay Officials</p>
                            <p class="mt-2 leading-6">Official accounts are issued by the city super admin and are not created through public registration.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">City Super Admin</p>
                            <p class="mt-2 leading-6">Administrative access is restricted and provisioned separately from resident self-registration.</p>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </main>
</body>
</html>
