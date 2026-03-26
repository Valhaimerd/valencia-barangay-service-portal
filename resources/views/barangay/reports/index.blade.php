@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Barangay Reports</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                {{ $barangay?->name }} Operational Reports
            </h1>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Document Status Counts</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($documentStatusCounts as $item)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$item['status']" />
                                <span class="font-medium text-slate-900">{{ $item['label'] }}</span>
                            </div>
                            <span class="text-lg font-bold text-slate-950">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Assistance Status Counts</h2>
                <div class="mt-6 space-y-3">
                    @foreach ($assistanceStatusCounts as $item)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <x-status-badge :status="$item['status']" />
                                <span class="font-medium text-slate-900">{{ $item['label'] }}</span>
                            </div>
                            <span class="text-lg font-bold text-slate-950">{{ $item['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Daily Submissions</h2>

                <div class="mt-6 space-y-3">
                    @forelse ($dailySubmissions as $item)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="font-medium text-slate-900">{{ \Illuminate\Support\Carbon::parse($item->report_date)->format('F d, Y') }}</span>
                            <span class="text-lg font-bold text-slate-950">{{ $item->total }}</span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                            No submission data yet.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Recent Request Activity</h2>

                <div class="mt-6 space-y-3">
                    @forelse ($recentRequests as $requestItem)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $requestItem->serviceType?->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $requestItem->reference_number }} · {{ $requestItem->residentProfile?->full_name ?? $requestItem->residentProfile?->user?->name }}
                                </p>
                            </div>
                            <x-status-badge :status="$requestItem->current_status" />
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
                            No request activity yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </section>
    </div>
@endsection
