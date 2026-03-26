@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Resident Portal
                    </p>

                    <h1 class="text-3xl font-bold tracking-tight text-slate-950">
                        Welcome, {{ $user->name }}
                    </h1>

                    <p class="max-w-3xl text-sm leading-7 text-slate-600">
                        Request center and submission flow are now active.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('resident.requests.create') }}"
                       class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Create Request
                    </a>

                    <a href="{{ route('resident.requests.index') }}"
                       class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Open Request History
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $requestStats['total'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Document Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $requestStats['documents'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Assistance Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $requestStats['assistance'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Completed</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $requestStats['completed'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Quick Access</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Resident request center and existing shell pages.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <a href="{{ route('resident.requests.create') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Create Request</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Open the service chooser and request submission form.</p>
                    </a>

                    <a href="{{ route('resident.profile.show') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Profile</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">View resident profile shell and verification details.</p>
                    </a>

                    <a href="{{ route('resident.requests.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Request History</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Open submitted resident requests and detail pages.</p>
                    </a>

                    <a href="{{ route('resident.notifications.index') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                        <h3 class="text-lg font-semibold text-slate-900">Notifications</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Open resident-facing notices and portal updates.</p>
                    </a>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Resident Summary</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Current authenticated resident context.
                    </p>
                </div>

                <div class="mt-6 space-y-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Email</span>
                        <span class="font-medium text-slate-900">{{ $user->email }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Role</span>
                        <span class="font-medium text-slate-900">{{ $user->role }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Barangay</span>
                        <span class="font-medium text-slate-900">{{ $residentProfile?->barangay?->name ?? 'Not yet set' }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Profile Record</span>
                        <span class="font-medium text-slate-900">{{ $residentProfile ? 'Available' : 'Not yet created' }}</span>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Recent Requests</h3>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentRequests as $recentRequest)
                            <a href="{{ route('resident.requests.show', $recentRequest->reference_number) }}"
                               class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 hover:bg-white">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $recentRequest->serviceType?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $recentRequest->reference_number }}</p>
                                </div>

                                <x-status-badge :status="$recentRequest->current_status" />
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                                No requests yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
