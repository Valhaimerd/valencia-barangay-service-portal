@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Official Accounts</p>
                    <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Barangay Officials</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        City-super-admin-controlled official account management.
                    </p>
                </div>

                <a href="{{ route('super_admin.officials.create') }}"
                   class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                    Create Official Account
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            @if ($officials->count() === 0)
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No official accounts yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-4 py-3 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Barangay</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="px-4 py-3 font-medium">Employee Code</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($officials as $official)
                                <tr class="align-top">
                                    <td class="px-4 py-4 font-medium text-slate-900">
                                        {{ $official->user?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $official->user?->email }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $official->barangay?->name }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ config('portal.official_roles.' . $official->official_role, $official->official_role) }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-700">
                                        {{ $official->employee_code ?: '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-status-badge :status="$official->user?->account_status ?? 'deactivated'" />
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('super_admin.officials.edit', $official) }}"
                                           class="font-medium text-slate-900 hover:text-slate-700">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $officials->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
