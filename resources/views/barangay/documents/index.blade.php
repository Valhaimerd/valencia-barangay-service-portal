@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Document Requests</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Certificate Workflow Queue</h1>
                </div>

                <form method="GET" action="{{ route('barangay.documents.index') }}" class="grid gap-3 md:grid-cols-[1fr_240px_auto]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search reference or resident"
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
            @if ($documents->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No document requests found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Reference</th>
                                <th class="px-4 py-3 font-medium">Resident</th>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Purpose</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Submitted</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($documents as $document)
                                <tr class="align-top">
                                    <td class="px-4 py-4 font-medium text-slate-900">
                                        {{ $document->reference_number }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $document->residentProfile?->full_name ?? $document->residentProfile?->user?->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $document->residentProfile?->user?->email }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $document->serviceType?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ \Illuminate\Support\Str::limit($document->documentDetail?->purpose, 60) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$document->current_status" />
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $document->submitted_at?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col items-start gap-2">
                                            <a href="{{ route('barangay.documents.show', $document) }}"
                                               class="font-medium text-slate-900 hover:text-slate-700">
                                                Open
                                            </a>

                                            @if (in_array($document->current_status, ['for_printing', 'ready_for_pickup', 'released'], true))
                                                <a href="{{ route('barangay.documents.print', $document) }}"
                                                   target="_blank"
                                                   class="font-medium text-slate-700 hover:text-slate-900">
                                                    Print
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
