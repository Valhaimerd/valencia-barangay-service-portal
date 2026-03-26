@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Notifications</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Resident Notifications</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Real-time resident notices from verification and request workflow updates.
                    </p>
                </div>

                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('resident.notifications.read_all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                            Mark All Read ({{ $unreadCount }})
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <section class="space-y-4">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $link = $data['link'] ?? route('resident.dashboard');
                @endphp

                <a href="{{ $link }}"
                   class="block rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ $data['title'] ?? 'Notification' }}
                                </h2>

                                @if (is_null($notification->read_at))
                                    <span class="inline-flex items-center rounded-full bg-slate-900 px-2.5 py-1 text-xs font-semibold text-white">
                                        Unread
                                    </span>
                                @endif
                            </div>

                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                {{ $data['message'] ?? 'No notification message.' }}
                            </p>
                        </div>

                        <x-status-badge :status="$data['type'] ?? 'info'" />
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        {{ $notification->created_at?->format('F d, Y h:i A') }}
                    </p>
                </a>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-500">
                    No notifications available.
                </div>
            @endforelse

            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        </section>
    </div>
@endsection
