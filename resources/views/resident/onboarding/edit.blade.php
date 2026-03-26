@extends('layouts.resident')

@section('content')
    <div class="space-y-8">
        <section class="rounded-3xl border border-orange-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Correction Required</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Update and Resubmit Registration</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Edit your resident information and resubmit the required verification files.
            </p>
        </section>

        @include('resident.onboarding._form', [
            'mode' => 'edit',
            'submitRoute' => route('resident.onboarding.update'),
            'residentProfile' => $residentProfile,
            'verification' => $verification,
            'existingFiles' => $existingFiles,
            'barangays' => $barangays,
            'submitLabel' => 'Resubmit for Review',
        ])
    </div>
@endsection
