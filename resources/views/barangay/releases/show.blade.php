@extends('layouts.barangay')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Release Operation</p>
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
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Release Summary</h2>

                    <div class="mt-6 grid gap-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Resident</span>
                            <span class="font-medium text-slate-900">{{ $residentProfile->full_name }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Type</span>
                            <span class="font-medium text-slate-900">{{ ucfirst($serviceRequest->request_category) }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Status</span>
                            <span class="font-medium text-slate-900">{{ str($serviceRequest->current_status)->replace('_', ' ')->title() }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Document Number</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->generatedDocument?->document_number ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Released To</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_to_name ?: '—' }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500">Released At</span>
                            <span class="font-medium text-slate-900">{{ $serviceRequest->releaseRecord?->released_at?->format('F d, Y h:i A') ?: '—' }}</span>
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
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Record Release</h2>

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

                <form method="POST" action="{{ route('barangay.releases.update', $serviceRequest) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="released_to_name" class="text-sm font-medium text-slate-700">Released To Name</label>
                        <input id="released_to_name"
                               name="released_to_name"
                               type="text"
                               value="{{ old('released_to_name', $serviceRequest->releaseRecord?->released_to_name) }}"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="released_to_relationship" class="text-sm font-medium text-slate-700">Relationship</label>
                        <input id="released_to_relationship"
                               name="released_to_relationship"
                               type="text"
                               value="{{ old('released_to_relationship', $serviceRequest->releaseRecord?->released_to_relationship) }}"
                               class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>

                    <div>
                        <label for="claimant_identification_notes" class="text-sm font-medium text-slate-700">Claimant Identification Notes</label>
                        <textarea id="claimant_identification_notes"
                                  name="claimant_identification_notes"
                                  rows="4"
                                  class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('claimant_identification_notes', $serviceRequest->releaseRecord?->claimant_identification_notes) }}</textarea>
                    </div>

                    <div>
                        <label for="remarks" class="text-sm font-medium text-slate-700">Remarks</label>
                        <textarea id="remarks"
                                  name="remarks"
                                  rows="4"
                                  class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('remarks', $serviceRequest->releaseRecord?->remarks) }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('barangay.releases.index') }}"
                           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Back to Release Queue
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Save Release
                        </button>
                    </div>
                </form>
            </section>
        </section>
    </div>
@endsection
