@props(['status'])

@php
    $normalized = is_string($status) ? strtolower(trim($status)) : 'unknown';

    $map = [
        'submitted' => ['label' => 'Submitted', 'classes' => 'border-sky-200 bg-sky-50 text-sky-700'],
        'under_review' => ['label' => 'Under Review', 'classes' => 'border-blue-200 bg-blue-50 text-blue-700'],
        'approved' => ['label' => 'Approved', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'for_payment' => ['label' => 'For Payment', 'classes' => 'border-amber-200 bg-amber-50 text-amber-700'],
        'for_printing' => ['label' => 'For Printing', 'classes' => 'border-indigo-200 bg-indigo-50 text-indigo-700'],
        'ready_for_pickup' => ['label' => 'Ready for Pickup', 'classes' => 'border-green-200 bg-green-50 text-green-700'],
        'ready_for_claim' => ['label' => 'Ready for Claim', 'classes' => 'border-green-200 bg-green-50 text-green-700'],
        'released' => ['label' => 'Released', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'closed' => ['label' => 'Closed', 'classes' => 'border-slate-200 bg-slate-100 text-slate-700'],
        'referred' => ['label' => 'Referred', 'classes' => 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700'],
        'needs_additional_documents' => ['label' => 'Needs Additional Documents', 'classes' => 'border-orange-200 bg-orange-50 text-orange-700'],
        'pending_verification' => ['label' => 'Pending Verification', 'classes' => 'border-sky-200 bg-sky-50 text-sky-700'],
        'needs_correction' => ['label' => 'Needs Correction', 'classes' => 'border-amber-200 bg-amber-50 text-amber-700'],
        'verified' => ['label' => 'Verified', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'rejected' => ['label' => 'Rejected', 'classes' => 'border-rose-200 bg-rose-50 text-rose-700'],
        'cancelled' => ['label' => 'Cancelled', 'classes' => 'border-rose-200 bg-rose-50 text-rose-700'],
        'pending' => ['label' => 'Pending', 'classes' => 'border-amber-200 bg-amber-50 text-amber-700'],
        'accepted' => ['label' => 'Accepted', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'paid' => ['label' => 'Paid', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'void' => ['label' => 'Void', 'classes' => 'border-rose-200 bg-rose-50 text-rose-700'],
        'completed' => ['label' => 'Completed', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'active' => ['label' => 'Active', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'inactive' => ['label' => 'Inactive', 'classes' => 'border-slate-200 bg-slate-100 text-slate-700'],
        'warning' => ['label' => 'Warning', 'classes' => 'border-amber-200 bg-amber-50 text-amber-700'],
        'info' => ['label' => 'Info', 'classes' => 'border-sky-200 bg-sky-50 text-sky-700'],
        'success' => ['label' => 'Success', 'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700'],
        'document' => ['label' => 'Document', 'classes' => 'border-indigo-200 bg-indigo-50 text-indigo-700'],
        'assistance' => ['label' => 'Assistance', 'classes' => 'border-purple-200 bg-purple-50 text-purple-700'],
    ];

    $resolved = $map[$normalized] ?? [
        'label' => \Illuminate\Support\Str::of((string) $status)->replace('_', ' ')->title()->toString(),
        'classes' => 'border-slate-200 bg-slate-100 text-slate-700',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold',
    $resolved['classes'],
]) }}>
    {{ $resolved['label'] }}
</span>
