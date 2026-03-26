@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Payment Operations</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Payment Queue</h1>
                </div>

                <form method="GET" action="{{ route('barangay.payments.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search reference or resident"
                           class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">

                    <select name="status"
                            class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        <option value="">Default queue</option>
                        <option value="approved" @selected($selectedStatus === 'approved')>Approved</option>
                        <option value="for_payment" @selected($selectedStatus === 'for_payment')>For Payment</option>
                        <option value="for_printing" @selected($selectedStatus === 'for_printing')>For Printing</option>
                    </select>

                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Filter
                    </button>
                </form>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @if ($payments->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No payment records found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Reference</th>
                                <th class="px-4 py-3 font-medium">Resident</th>
                                <th class="px-4 py-3 font-medium">Service</th>
                                <th class="px-4 py-3 font-medium">Amount</th>
                                <th class="px-4 py-3 font-medium">OR Number</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($payments as $paymentRequest)
                                @php
                                    $latestPayment = $paymentRequest->paymentRecords->sortByDesc('paid_at')->first();
                                @endphp
                                <tr class="align-top">
                                    <td class="px-4 py-4 font-medium text-slate-900">{{ $paymentRequest->reference_number }}</td>
                                    <td class="px-4 py-4">
                                        <div class="font-medium text-slate-900">
                                            {{ $paymentRequest->residentProfile?->full_name ?? $paymentRequest->residentProfile?->user?->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $paymentRequest->residentProfile?->user?->email }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">{{ $paymentRequest->serviceType?->name }}</td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $paymentRequest->documentDetail?->payment_amount !== null ? number_format($paymentRequest->documentDetail->payment_amount, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">{{ $latestPayment?->official_receipt_number ?: '—' }}</td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$paymentRequest->current_status" />
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('barangay.payments.show', $paymentRequest) }}"
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
                    {{ $payments->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
