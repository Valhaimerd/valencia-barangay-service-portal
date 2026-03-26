@props([
    'logs' => collect(),
    'currentStatus' => null,
])

@php
    $timelineItems = collect($logs ?? [])
        ->sortBy('acted_at')
        ->values();

    if ($timelineItems->isEmpty() && $currentStatus) {
        $timelineItems = collect([
            (object) [
                'from_status' => null,
                'to_status' => $currentStatus,
                'remarks' => 'Current request status.',
                'acted_at' => null,
                'actedBy' => null,
            ],
        ]);
    }
@endphp

<div class="space-y-4">
    @forelse ($timelineItems as $item)
        <div class="flex gap-4">
            <div class="flex flex-col items-center">
                <div class="mt-1 h-3 w-3 rounded-full bg-slate-900"></div>
                @if (! $loop->last)
                    <div class="mt-2 h-full min-h-10 w-px bg-slate-200"></div>
                @endif
            </div>

            <div class="flex-1 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($item->from_status)
                            <x-status-badge :status="$item->from_status" />
                            <span class="text-slate-400">→</span>
                        @endif
                        <x-status-badge :status="$item->to_status" />
                    </div>

                    <p class="text-xs text-slate-500">
                        {{ $item->acted_at ? \Illuminate\Support\Carbon::parse($item->acted_at)->format('F d, Y h:i A') : 'Pending timestamp' }}
                    </p>
                </div>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    {{ $item->remarks ?: 'No remarks recorded.' }}
                </p>

                <p class="mt-3 text-xs text-slate-500">
                    By: {{ $item->actedBy?->name ?? 'System / Pending assignment' }}
                </p>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">
            No timeline data yet.
        </div>
    @endforelse
</div>
