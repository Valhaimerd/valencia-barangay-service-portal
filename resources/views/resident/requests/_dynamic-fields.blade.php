<section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <h2 class="text-2xl font-bold tracking-tight text-slate-900">Service-Specific Data</h2>

    <div class="mt-6 grid gap-5">
        @foreach (($schema['fields'] ?? []) as $fieldKey => $field)
            @php
                $type = $field['type'] ?? 'text';
                $label = $field['label'] ?? \Illuminate\Support\Str::of($fieldKey)->replace('_', ' ')->title()->toString();
                $defaultValue = $field['default'] ?? null;
                $value = old($fieldKey, $defaultValue);
            @endphp

            @if ($type === 'textarea')
                <div>
                    <label for="{{ $fieldKey }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>
                    <textarea id="{{ $fieldKey }}"
                              name="{{ $fieldKey }}"
                              rows="{{ $field['rows'] ?? 5 }}"
                              class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ $value }}</textarea>
                </div>
            @elseif ($type === 'checkbox')
                <label class="inline-flex items-center gap-3 text-sm text-slate-700">
                    <input type="checkbox"
                           name="{{ $fieldKey }}"
                           value="1"
                           @checked(old($fieldKey, $defaultValue))
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span>{{ $label }}</span>
                </label>
            @else
                <div>
                    <label for="{{ $fieldKey }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>
                    <input id="{{ $fieldKey }}"
                           name="{{ $fieldKey }}"
                           type="{{ $type }}"
                           @isset($field['min']) min="{{ $field['min'] }}" @endisset
                           @isset($field['max']) max="{{ $field['max'] }}" @endisset
                           @isset($field['step']) step="{{ $field['step'] }}" @endisset
                           value="{{ $value }}"
                           class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                </div>
            @endif
        @endforeach
    </div>
</section>
