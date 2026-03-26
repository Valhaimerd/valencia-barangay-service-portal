<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Portal Entry
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-6">
            <div class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm sm:p-8">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Authenticated Entry Point
                </h1>
                <p class="mt-2 text-slate-600">
                    Part 0 keeps this page neutral. Role-based redirects and restrictions
                    will be added in later parts after the official role structure and
                    resident verification logic are implemented.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <a href="{{ route('resident.dashboard') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                    <h3 class="text-lg font-semibold text-slate-900">Resident Portal</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Onboarding, request submission, request history, and notifications.
                    </p>
                </a>

                <a href="{{ route('barangay.dashboard') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                    <h3 class="text-lg font-semibold text-slate-900">Barangay Portal</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Verification queues, request processing, printing, release, and case handling.
                    </p>
                </a>

                <a href="{{ route('super_admin.dashboard') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                    <h3 class="text-lg font-semibold text-slate-900">City Super Admin Portal</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Official account governance, assignments, and system-level oversight.
                    </p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
