<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resident Sign Up - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-7xl items-center px-6 py-12">
        <div class="grid w-full gap-8 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm lg:p-10">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resident Registration</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Create a resident account</h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    This registration form is for residents only. After creating your account, you will continue to resident onboarding and verification.
                </p>

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

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="text-sm font-medium text-slate-700">Full Name</label>
                        <input id="name"
                               name="name"
                               type="text"
                               value="{{ old('name') }}"
                               required
                               autofocus
                               autocomplete="name"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="email" class="text-sm font-medium text-slate-700">Email Address</label>
                        <input id="email"
                               name="email"
                               type="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="username"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                        <input id="password"
                               name="password"
                               type="password"
                               required
                               autocomplete="new-password"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-sm font-medium text-slate-700">Confirm Password</label>
                        <input id="password_confirmation"
                               name="password_confirmation"
                               type="password"
                               required
                               autocomplete="new-password"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('login', ['portal' => 'resident']) }}"
                           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Already have an account?
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Create Resident Account
                        </button>
                    </div>
                </form>
            </section>

            <section class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">What happens next</h2>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">1. Account creation</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Your account is created as a resident account only.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">2. Resident onboarding</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">You will be redirected to complete profile, address, verification, and file submission details.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-semibold text-slate-900">3. Verification review</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Barangay verifiers review your registration before service access is fully unlocked.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Important</h2>
                    <div class="mt-6 space-y-3 text-sm leading-7 text-slate-600">
                        <p>Barangay official and city super admin accounts are not created here.</p>
                        <p>Those accounts are provisioned through internal administrative workflow.</p>
                    </div>
                </section>
            </section>
        </div>
    </main>
</body>
</html>
