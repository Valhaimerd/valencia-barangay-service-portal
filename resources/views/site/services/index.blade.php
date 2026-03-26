@extends('layouts.public')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                Service Catalog
            </p>

            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">
                Supported Services
            </h1>

            <p class="mt-3 max-w-3xl text-base leading-7 text-slate-600">
                Public service overview and resident request entry.
            </p>
        </section>

        @foreach (['document' => 'Document Services', 'assistance' => 'Assistance Services'] as $groupKey => $groupTitle)
            <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $groupTitle }}</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ $groupKey === 'document' ? 'Certificate and certification requests.' : 'Case-based resident support requests.' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse (($services[$groupKey] ?? collect()) as $service)
                        @php
                            $schema = \App\Support\ServiceRequestSchema::for($service);
                            $requiredAttachmentCount = collect($schema['attachments'] ?? [])->where('required', true)->count();
                        @endphp

                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">
                                        {{ $service->name }}
                                    </h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        {{ $schema['description'] ?: ('Code: ' . $service->code) }}
                                    </p>
                                </div>

                                <x-status-badge :status="$service->is_active ? 'active' : 'inactive'" />
                            </div>

                            <div class="mt-5 space-y-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span>Category</span>
                                    <span class="font-medium text-slate-800">{{ ucfirst($service->category) }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span>Payment Required</span>
                                    <span class="font-medium text-slate-800">
                                        {{ $service->requires_payment ? 'Yes' : 'No' }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span>Service Fields</span>
                                    <span class="font-medium text-slate-800">{{ count($schema['fields'] ?? []) }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span>Required Attachments</span>
                                    <span class="font-medium text-slate-800">{{ $requiredAttachmentCount }}</span>
                                </div>
                            </div>

                            @auth
                                @if (auth()->user()->isResident())
                                    <div class="mt-5">
                                        <a href="{{ route('resident.requests.create.service', $service) }}"
                                           class="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                                            Start Request
                                        </a>
                                    </div>
                                @endif
                            @endauth
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                            No services found.
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection
