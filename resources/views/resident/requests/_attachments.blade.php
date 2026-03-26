<section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Required Attachments</h2>

    <div class="mt-6 grid gap-5 md:grid-cols-2">
        @foreach (($schema['attachments'] ?? []) as $attachmentKey => $attachment)
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ $attachment['label'] }}</h3>
                        <p class="mt-2 text-xs text-slate-500">
                            Accepted: JPG, JPEG, PNG, WEBP, PDF. Max 5MB.
                        </p>
                    </div>

                    <x-status-badge :status="($attachment['required'] ?? false) ? 'approved' : 'pending'" />
                </div>

                <div class="mt-4">
                    <input id="attachment_{{ $attachmentKey }}"
                           name="attachments[{{ $attachmentKey }}]"
                           type="file"
                           class="block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 border-t border-slate-200 pt-8">
        <h3 class="text-lg font-semibold text-slate-900">Other Supporting Files</h3>
        <div class="mt-4">
            <input id="other_supporting_files"
                   name="other_supporting_files[]"
                   type="file"
                   multiple
                   class="block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
            <p class="mt-2 text-xs text-slate-500">Optional additional files that may help with review.</p>
        </div>
    </div>
</section>
