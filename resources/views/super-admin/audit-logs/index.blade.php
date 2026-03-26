@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Audit Trail</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">System Audit Logs</h1>
                </div>

                <form method="GET" action="{{ route('super_admin.audit_logs.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search user, action, or description"
                           class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">

                    <select name="action"
                            class="rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        <option value="">All actions</option>
                        @foreach ($actionOptions as $actionOption)
                            <option value="{{ $actionOption }}" @selected($selectedAction === $actionOption)>{{ $actionOption }}</option>
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
            @if ($logs->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No audit log records found.
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($logs as $log)
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h2 class="text-lg font-semibold text-slate-900">{{ $log->action }}</h2>
                                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ $log->auditable_type ?: 'General' }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm leading-7 text-slate-600">
                                        {{ $log->description ?: 'No description recorded.' }}
                                    </p>

                                    <div class="mt-3 text-xs text-slate-500 space-y-1">
                                        <p>User: {{ $log->user?->name ?? 'System' }}{{ $log->user?->email ? ' · ' . $log->user->email : '' }}</p>
                                        <p>IP: {{ $log->ip_address ?: '—' }}</p>
                                        <p>At: {{ $log->created_at?->format('F d, Y h:i A') }}</p>
                                    </div>
                                </div>

                                <div class="w-full max-w-md space-y-3">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Old Values</p>
                                        <pre class="mt-3 overflow-x-auto text-xs text-slate-700">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">New Values</p>
                                        <pre class="mt-3 overflow-x-auto text-xs text-slate-700">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: 'null' }}</pre>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
