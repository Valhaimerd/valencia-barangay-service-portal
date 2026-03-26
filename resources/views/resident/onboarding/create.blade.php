@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Resident Onboarding</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Complete Your Resident Registration</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Fill out all resident, address, identity, and face capture fields before submission.
            </p>
        </section>

        @include('resident.onboarding._form', [
            'mode' => 'create',
            'submitRoute' => route('resident.onboarding.store'),
            'residentProfile' => $residentProfile,
            'verification' => $verification,
            'existingFiles' => $existingFiles,
            'barangays' => $barangays,
            'submitLabel' => 'Submit for Verification',
        ])
    </div>
@endsection
