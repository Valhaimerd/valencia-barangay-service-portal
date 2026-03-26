@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Request History</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Resident Requests</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Submitted service requests and tracking records.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('resident.requests.create') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Create New Request
                    </a>

                    <a href="{{ route('services.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Browse Services
                    </a>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @if (! $residentProfile)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No resident profile record yet.
                </div>
            @elseif ($requests instanceof \Illuminate\Support\Collection && $requests->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No requests yet.
                </div>
            @elseif (method_exists($requests, 'count') && $requests->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No requests yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Reference</th>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Barangay</th>
                                <th class="px-4 py-3 font-medium">Category</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Submitted</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($requests as $serviceRequest)
                                <tr class="align-top">
                                    <td class="px-4 py-4 font-medium text-slate-900">
                                        {{ $serviceRequest->reference_number }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $serviceRequest->serviceType?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $serviceRequest->barangay?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ ucfirst($serviceRequest->request_category) }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$serviceRequest->current_status" />
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $serviceRequest->submitted_at?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('resident.requests.show', $serviceRequest->reference_number) }}"
                                           class="font-medium text-slate-900 hover:text-slate-700">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (method_exists($requests, 'links'))
                    <div class="mt-6">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
