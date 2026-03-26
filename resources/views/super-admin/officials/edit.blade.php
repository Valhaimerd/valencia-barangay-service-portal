@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Official Accounts</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Edit Official Account</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                {{ $userAccount->name }} · {{ $userAccount->email }}
            </p>
        </section>

        @include('super-admin.officials._form', [
            'mode' => 'edit',
            'submitRoute' => route('super_admin.officials.update', $official),
            'official' => $official,
            'userAccount' => $userAccount,
            'barangays' => $barangays,
            'submitLabel' => 'Update Official Account',
        ])
    </div>
@endsection
