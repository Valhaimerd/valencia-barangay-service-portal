@php
    $selectedMethod = old('verification_method', $verification?->verification_method ?? 'government_id');
@endphp

<form method="POST"
      action="{{ $submitRoute }}"
      enctype="multipart/form-data"
      x-data="{ step: 1, verificationMethod: '{{ $selectedMethod }}', sameAsCurrent: false }"
      class="space-y-8">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    @if ($errors->any())
        <section class="rounded-3xl border border-rose-200 bg-rose-50 p-6">
            <h2 class="text-lg font-semibold text-rose-700">Please fix the highlighted fields.</h2>
            <ul class="mt-3 space-y-1 text-sm text-rose-600">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-3 md:grid-cols-4">
            @foreach ([
                1 => 'Resident Information',
                2 => 'Address',
                3 => 'Identity Verification',
                4 => 'Face Capture',
            ] as $stepNumber => $stepLabel)
                <button type="button"
                        x-on:click="step = {{ $stepNumber }}"
                        x-bind:class="step === {{ $stepNumber }} ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-slate-50 text-slate-700'"
                        class="rounded-2xl border px-4 py-4 text-left transition">
                    <p class="text-xs font-semibold uppercase tracking-wide">Step {{ $stepNumber }}</p>
                    <p class="mt-2 text-sm font-semibold">{{ $stepLabel }}</p>
                </button>
            @endforeach
        </div>
    </section>

    <section x-show="step === 1" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Resident Information</h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <label for="first_name" class="text-sm font-medium text-slate-700">First Name</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $residentProfile?->first_name) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="middle_name" class="text-sm font-medium text-slate-700">Middle Name</label>
                <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name', $residentProfile?->middle_name) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="last_name" class="text-sm font-medium text-slate-700">Last Name</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $residentProfile?->last_name) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="suffix" class="text-sm font-medium text-slate-700">Suffix</label>
                <input id="suffix" name="suffix" type="text" value="{{ old('suffix', $residentProfile?->suffix) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="sex" class="text-sm font-medium text-slate-700">Sex</label>
                <select id="sex" name="sex"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select sex</option>
                    @foreach (config('portal.sex_options') as $sex)
                        <option value="{{ $sex }}" @selected(old('sex', $residentProfile?->sex) === $sex)>{{ $sex }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="birth_date" class="text-sm font-medium text-slate-700">Birth Date</label>
                <input id="birth_date" name="birth_date" type="date"
                       value="{{ old('birth_date', optional($residentProfile?->birth_date)->format('Y-m-d')) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="birth_place" class="text-sm font-medium text-slate-700">Birth Place</label>
                <input id="birth_place" name="birth_place" type="text" value="{{ old('birth_place', $residentProfile?->birth_place) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="civil_status" class="text-sm font-medium text-slate-700">Civil Status</label>
                <select id="civil_status" name="civil_status"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select civil status</option>
                    @foreach (config('portal.civil_status_options') as $civilStatus)
                        <option value="{{ $civilStatus }}" @selected(old('civil_status', $residentProfile?->civil_status) === $civilStatus)>{{ $civilStatus }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="mobile_number" class="text-sm font-medium text-slate-700">Mobile Number</label>
                <input id="mobile_number" name="mobile_number" type="text" value="{{ old('mobile_number', $residentProfile?->mobile_number) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="occupation" class="text-sm font-medium text-slate-700">Occupation</label>
                <input id="occupation" name="occupation" type="text" value="{{ old('occupation', $residentProfile?->occupation) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="citizenship" class="text-sm font-medium text-slate-700">Citizenship</label>
                <input id="citizenship" name="citizenship" type="text" value="{{ old('citizenship', $residentProfile?->citizenship ?? 'Filipino') }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="profile_photo" class="text-sm font-medium text-slate-700">Profile Photo (Optional)</label>
                <input id="profile_photo" name="profile_photo" type="file"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                @if ($residentProfile?->profile_photo_path)
                    <p class="mt-2 text-xs text-slate-500">Current file: {{ basename($residentProfile->profile_photo_path) }}</p>
                @endif
            </div>
        </div>
    </section>

    <section x-show="step === 2" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Address</h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="barangay_id" class="text-sm font-medium text-slate-700">Current Barangay</label>
                <select id="barangay_id" name="barangay_id"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select barangay</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay->id }}" @selected((string) old('barangay_id', $residentProfile?->barangay_id) === (string) $barangay->id)>
                            {{ $barangay->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="current_address_line" class="text-sm font-medium text-slate-700">Current Address Line</label>
                <textarea id="current_address_line" name="current_address_line" rows="3"
                          class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('current_address_line', $residentProfile?->current_address_line) }}</textarea>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Municipality</label>
                <input type="text" value="Valencia City" readonly
                       class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600 shadow-sm">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Province</label>
                <input type="text" value="Bukidnon" readonly
                       class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600 shadow-sm">
            </div>

            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" x-model="sameAsCurrent" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    <span>Permanent address is the same as current address</span>
                </label>
            </div>

            <div class="md:col-span-2">
                <label for="permanent_address_line" class="text-sm font-medium text-slate-700">Permanent Address Line</label>
                <textarea id="permanent_address_line" name="permanent_address_line" rows="3"
                          x-bind:value="sameAsCurrent ? $refs.currentAddress.value : '{{ old('permanent_address_line', $residentProfile?->permanent_address_line) }}'"
                          x-ref="permanentAddress"
                          class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">{{ old('permanent_address_line', $residentProfile?->permanent_address_line) }}</textarea>
            </div>

            <div style="display:none">
                <input x-ref="currentAddress" value="{{ old('current_address_line', $residentProfile?->current_address_line) }}">
            </div>

            <div>
                <label for="permanent_barangay_name" class="text-sm font-medium text-slate-700">Permanent Barangay</label>
                <input id="permanent_barangay_name" name="permanent_barangay_name" type="text"
                       value="{{ old('permanent_barangay_name', $residentProfile?->permanent_barangay_name) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="permanent_municipality" class="text-sm font-medium text-slate-700">Permanent Municipality</label>
                <input id="permanent_municipality" name="permanent_municipality" type="text"
                       value="{{ old('permanent_municipality', $residentProfile?->permanent_municipality) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="permanent_province" class="text-sm font-medium text-slate-700">Permanent Province</label>
                <input id="permanent_province" name="permanent_province" type="text"
                       value="{{ old('permanent_province', $residentProfile?->permanent_province) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>
        </div>
    </section>

    <section x-show="step === 3" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Identity Verification</h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="verification_method" class="text-sm font-medium text-slate-700">Verification Method</label>
                <select id="verification_method" name="verification_method" x-model="verificationMethod"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    @foreach (config('portal.verification_methods') as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="identity_document_label" class="text-sm font-medium text-slate-700">Identity Document Type</label>
                <select id="identity_document_label" name="identity_document_label"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select identity document type</option>
                    @foreach (config('portal.identity_document_labels') as $label)
                        <option value="{{ $label }}" @selected(old('identity_document_label', $verification?->identity_document_label) === $label)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="identity_document_number" class="text-sm font-medium text-slate-700">Identity Document Number</label>
                <input id="identity_document_number" name="identity_document_number" type="text"
                       value="{{ old('identity_document_number', $verification?->identity_document_number) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div class="md:col-span-2" x-show="verificationMethod === 'secondary_id_with_proof'">
                <label for="proof_of_residency_label" class="text-sm font-medium text-slate-700">Proof of Residency Type</label>
                <select id="proof_of_residency_label" name="proof_of_residency_label"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select proof of residency</option>
                    @foreach (config('portal.proof_of_residency_labels') as $label)
                        <option value="{{ $label }}" @selected(old('proof_of_residency_label', $verification?->proof_of_residency_label) === $label)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 grid gap-5 md:grid-cols-2">
                <div x-show="verificationMethod === 'government_id'">
                    <label for="government_id_file" class="text-sm font-medium text-slate-700">Government ID Upload</label>
                    <input id="government_id_file" name="government_id_file" type="file"
                           class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                    @if (($existingFiles['government_id'] ?? null))
                        <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['government_id']->original_name ?? basename($existingFiles['government_id']->file_path) }}</p>
                    @endif
                </div>

                <div x-show="verificationMethod === 'secondary_id_with_proof'">
                    <label for="secondary_id_file" class="text-sm font-medium text-slate-700">Secondary ID Upload</label>
                    <input id="secondary_id_file" name="secondary_id_file" type="file"
                           class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                    @if (($existingFiles['secondary_id'] ?? null))
                        <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['secondary_id']->original_name ?? basename($existingFiles['secondary_id']->file_path) }}</p>
                    @endif
                </div>

                <div x-show="verificationMethod === 'secondary_id_with_proof'">
                    <label for="proof_of_residency_file" class="text-sm font-medium text-slate-700">Proof of Residency Upload</label>
                    <input id="proof_of_residency_file" name="proof_of_residency_file" type="file"
                           class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                    @if (($existingFiles['proof_of_residency'] ?? null))
                        <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['proof_of_residency']->original_name ?? basename($existingFiles['proof_of_residency']->file_path) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section x-show="step === 4" class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Face Capture</h2>

        <div class="mt-6 grid gap-5 md:grid-cols-3">
            <div>
                <label for="face_front" class="text-sm font-medium text-slate-700">Front Face Capture</label>
                <input id="face_front" name="face_front" type="file"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                @if (($existingFiles['face_front'] ?? null))
                    <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['face_front']->original_name ?? basename($existingFiles['face_front']->file_path) }}</p>
                @endif
            </div>

            <div>
                <label for="face_left" class="text-sm font-medium text-slate-700">Left Face Capture</label>
                <input id="face_left" name="face_left" type="file"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                @if (($existingFiles['face_left'] ?? null))
                    <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['face_left']->original_name ?? basename($existingFiles['face_left']->file_path) }}</p>
                @endif
            </div>

            <div>
                <label for="face_right" class="text-sm font-medium text-slate-700">Right Face Capture</label>
                <input id="face_right" name="face_right" type="file"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-700">
                @if (($existingFiles['face_right'] ?? null))
                    <p class="mt-2 text-xs text-slate-500">Current file: {{ $existingFiles['face_right']->original_name ?? basename($existingFiles['face_right']->file_path) }}</p>
                @endif
            </div>
        </div>

        @if ($mode === 'edit' && $verification)
            <div class="mt-8 rounded-2xl border border-orange-200 bg-orange-50 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-orange-700">Previous Correction Notes</h3>
                <p class="mt-3 text-sm leading-6 text-orange-700">
                    {{ $verification->correction_notes ?: 'No correction notes recorded.' }}
                </p>
            </div>
        @endif
    </section>

    <section class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-3">
            <button type="button"
                    x-show="step > 1"
                    x-on:click="step = Math.max(1, step - 1)"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Previous
            </button>

            <button type="button"
                    x-show="step < 4"
                    x-on:click="step = Math.min(4, step + 1)"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800">
                Next
            </button>
        </div>

        <button type="submit"
                x-show="step === 4"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
            {{ $submitLabel }}
        </button>
    </section>
</form>
