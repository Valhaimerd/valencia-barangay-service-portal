@extends('layouts.super-admin')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Official Accounts</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Create Official Account</h1>
        </section>

        @include('super-admin.officials._form', [
            'mode' => 'create',
            'submitRoute' => route('super_admin.officials.store'),
            'official' => $official,
            'userAccount' => $userAccount,
            'barangays' => $barangays,
            'submitLabel' => 'Create Official Account',
        ])
    </div>
@endsection
