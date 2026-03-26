@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resident Verifications</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Verification Queue</h1>
                </div>

                <form method="GET" action="{{ route('barangay.verifications.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search resident name"
                           class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">

                    <select name="status"
                            class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        <option value="">All statuses</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Filter
                    </button>
                </form>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @if ($verifications->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No resident verification records found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Resident</th>
                                <th class="px-4 py-3 font-medium">Barangay</th>
                                <th class="px-4 py-3 font-medium">Method</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Submitted</th>
                                <th class="px-4 py-3 font-medium">Reviewed</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($verifications as $verification)
                                <tr class="align-top">
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $verification->residentProfile?->full_name ?? $verification->residentProfile?->user?->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $verification->residentProfile?->user?->email }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $verification->residentProfile?->barangay?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ config('portal.verification_methods.' . $verification->verification_method, $verification->verification_method) }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$verification->status" />
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $verification->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $verification->reviewed_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('barangay.verifications.show', $verification) }}"
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
                    {{ $verifications->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
