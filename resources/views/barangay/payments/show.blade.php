@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Payment Operation</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                        {{ $serviceRequest->serviceType?->name }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $serviceRequest->reference_number }} · {{ $residentProfile->full_name }}
                    </p>
                </div>

                <x-status-badge :status="$serviceRequest->current_status" />
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Request Summary</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Resident</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->full_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Service</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->serviceType?->name }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-slate-500">Purpose</span>
                            <span class="max-w-[70%] text-right font-medium text-slate-900">{{ $documentDetail?->purpose ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Current Amount</span>
                            <span class="font-medium text-slate-900">
                                {{ $documentDetail?->payment_amount !== null ? number_format($documentDetail->payment_amount, 2) : '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Latest OR Number</span>
                            <span class="font-medium text-slate-900">{{ $latestPayment?->official_receipt_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Paid At</span>
                            <span class="font-medium text-slate-900">{{ $latestPayment?->paid_at?->format('F d, Y h:i A') ?: '—' }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Status Timeline</h2>
                            <p class="mt-2 text-sm text-slate-500">Operational status history.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-request-timeline :logs="$serviceRequest->statusLogs" :currentStatus="$serviceRequest->current_status" />
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Record Payment</h2>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-rose-700">Please fix the highlighted fields.</h3>
                        <ul class="mt-3 space-y-1 text-sm text-rose-700">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('barangay.payments.update', $serviceRequest) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="payment_amount" class="text-sm font-medium text-slate-700">Payment Amount</label>
                        <input id="payment_amount"
                               name="payment_amount"
                               type="number"
                               min="0"
                               step="0.01"
                               value="{{ old('payment_amount', $documentDetail?->payment_amount) }}"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="official_receipt_number" class="text-sm font-medium text-slate-700">Official Receipt Number</label>
                        <input id="official_receipt_number"
                               name="official_receipt_number"
                               type="text"
                               value="{{ old('official_receipt_number', $latestPayment?->official_receipt_number ?? $documentDetail?->official_receipt_number) }}"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="notes" class="text-sm font-medium text-slate-700">Notes</label>
                        <textarea id="notes"
                                  name="notes"
                                  rows="4"
                                  class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('notes', $latestPayment?->notes) }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('barangay.payments.index') }}"
                           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Back to Payment Queue
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Confirm Payment
                        </button>
                    </div>
                </form>
            </section>
        </section>
    </div>
@endsection
