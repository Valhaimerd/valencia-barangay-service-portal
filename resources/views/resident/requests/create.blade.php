@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        @if (! $serviceType)
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Request Center</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Choose a Service</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                    Select one service to start a resident request.
                </p>
            </section>

            @foreach (['document' => 'Document Services', 'assistance' => 'Assistance Services'] as $groupKey => $groupTitle)
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $groupTitle }}</h2>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach (($services[$groupKey] ?? collect()) as $service)
                            @php
                                $serviceSchema = \App\Support\ServiceRequestSchema::for($service);
                            @endphp

                            <a href="{{ route('resident.requests.create.service', $service) }}"
                               class="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">{{ $service->name }}</h3>
                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $serviceSchema['description'] ?: ucfirst($service->category) }}
                                        </p>
                                    </div>

                                    <x-status-badge :status="$service->requires_payment ? 'for_payment' : 'active'" />
                                </div>

                                <div class="mt-5 space-y-2 text-xs text-slate-500">
                                    <p>Fields: {{ count($serviceSchema['fields'] ?? []) }}</p>
                                    <p>Required attachments: {{ collect($serviceSchema['attachments'] ?? [])->where('required', true)->count() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @else
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Request Center</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ $serviceType->name }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                            {{ $schema['description'] ?: 'Complete the request form and upload the required attachments.' }}
                        </p>
                    </div>

                    <x-status-badge :status="$serviceType->category" />
                </div>
            </section>

            @if ($errors->any())
                <section class="rounded-3xl border border-rose-200 bg-rose-50 p-6">
                    <h2 class="text-lg font-semibold text-rose-700">Please fix the highlighted fields.</h2>
                    <ul class="mt-3 space-y-1 text-sm text-rose-600">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <form method="POST" action="{{ route('resident.requests.store') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <input type="hidden" name="service_type_code" value="{{ $serviceType->code }}">

                @include('resident.requests._dynamic-fields', [
                    'serviceType' => $serviceType,
                    'schema' => $schema,
                ])

                @include('resident.requests._attachments', [
                    'serviceType' => $serviceType,
                    'schema' => $schema,
                ])

                @if (! empty($schema['review_checklist']))
                    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Reviewer Checklist Preview</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($schema['review_checklist'] as $checkItem)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    {{ $checkItem }}
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('resident.requests.create') }}"
                       class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Back to Service Chooser
                    </a>

                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
                        Submit Request
                    </button>
                </section>
            </form>
        @endif
    </div>
@endsection
