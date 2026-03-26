@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Release Operations</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Release Queue</h1>
                </div>

                <form method="GET" action="{{ route('barangay.releases.index') }}" class="grid gap-3 md:grid-cols-[1fr_180px_220px_auto]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search reference or resident"
                           class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">

                    <select name="type"
                            class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        <option value="">All types</option>
                        <option value="document" @selected($selectedType === 'document')>Document</option>
                        <option value="assistance" @selected($selectedType === 'assistance')>Assistance</option>
                    </select>

                    <select name="status"
                            class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        <option value="">Default queue</option>
                        <option value="ready_for_pickup" @selected($selectedStatus === 'ready_for_pickup')>Ready for Pickup</option>
                        <option value="ready_for_claim" @selected($selectedStatus === 'ready_for_claim')>Ready for Claim</option>
                        <option value="released" @selected($selectedStatus === 'released')>Released</option>
                    </select>

                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Filter
                    </button>
                </form>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @if ($releases->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No release records found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Reference</th>
                                <th class="px-4 py-3 font-medium">Resident</th>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Type</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Released To</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($releases as $releaseRequest)
                                <tr class="align-top">
                                    <td class="px-4 py-4 font-medium text-slate-900">{{ $releaseRequest->reference_number }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $releaseRequest->residentProfile?->full_name ?? $releaseRequest->residentProfile?->user?->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $releaseRequest->residentProfile?->user?->email }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">{{ $releaseRequest->serviceType?->name }}</td>
                                    <td class="px-4 py-4 text-slate-700">{{ ucfirst($releaseRequest->request_category) }}</td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$releaseRequest->current_status" />
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">{{ $releaseRequest->releaseRecord?->released_to_name ?: '—' }}</td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('barangay.releases.show', $releaseRequest) }}"
                                           class="font-medium text-slate-900 hover:text-slate-700">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $releases->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
