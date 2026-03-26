@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">System Reports</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">City-Level Overview</h1>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Residents</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['total_residents'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Verified Residents</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['verified_residents'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Officials</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['total_officials'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['total_requests'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Document Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['document_requests'] }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Assistance Requests</p>
                <p class="mt-3 text-3xl font-bold text-slate-950">{{ $stats['assistance_requests'] }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Barangay Coverage Summary</h2>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Barangay</th>
                                <th class="px-4 py-3 font-medium">Residents</th>
                                <th class="px-4 py-3 font-medium">Officials</th>
                                <th class="px-4 py-3 font-medium">Requests</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($barangaySummaries as $barangay)
                                <tr>
                                    <td class="px-4 py-4 font-medium text-slate-900">{{ $barangay->name }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $barangay->resident_count }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $barangay->official_count }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ $barangay->request_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Service Demand</h2>

                <div class="mt-6 space-y-3">
                    @foreach ($serviceSummaries as $service)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $service->name }}</p>
                                <p class="text-xs text-slate-500">{{ ucfirst($service->category) }}</p>
                            </div>
                            <span class="text-lg font-bold text-slate-950">{{ $service->service_requests_count }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Requests</h2>

            <div class="mt-6 space-y-3">
                @forelse ($recentRequests as $requestItem)
                    <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $requestItem->serviceType?->name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $requestItem->reference_number }} · {{ $requestItem->barangay?->name }}
                            </p>
                        </div>
                        <x-status-badge :status="$requestItem->current_status" />
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                        No request data yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
