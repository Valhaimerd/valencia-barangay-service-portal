<form method="POST" action="{{ $submitRoute }}" class="space-y-8">
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

    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Account Details</h2>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <label for="name" class="text-sm font-medium text-slate-700">Full Name</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $userAccount->name) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="email" class="text-sm font-medium text-slate-700">Email Address</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $userAccount->email) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="password" class="text-sm font-medium text-slate-700">
                    {{ $mode === 'create' ? 'Password' : 'New Password (optional)' }}
                </label>
                <input id="password" name="password" type="password"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="password_confirmation" class="text-sm font-medium text-slate-700">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="barangay_id" class="text-sm font-medium text-slate-700">Barangay</label>
                <select id="barangay_id" name="barangay_id"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select barangay</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay->id }}"
                            @selected((string) old('barangay_id', $official->barangay_id) === (string) $barangay->id)>
                            {{ $barangay->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="official_role" class="text-sm font-medium text-slate-700">Official Role</label>
                <select id="official_role" name="official_role"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    <option value="">Select official role</option>
                    @foreach (config('portal.official_roles') as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('official_role', $official->official_role) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="employee_code" class="text-sm font-medium text-slate-700">Employee Code</label>
                <input id="employee_code" name="employee_code" type="text"
                       value="{{ old('employee_code', $official->employee_code) }}"
                       class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
            </div>

            <div>
                <label for="account_status" class="text-sm font-medium text-slate-700">Account Status</label>
                <select id="account_status" name="account_status"
                        class="mt-2 w-full rounded-xl border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    @foreach (config('portal.account_statuses') as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('account_status', $userAccount->account_status ?? 'active') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('super_admin.officials.index') }}"
           class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Back to Official Accounts
        </a>

        <button type="submit"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-medium text-white hover:bg-slate-800">
            {{ $submitLabel }}
        </button>
    </section>
</form>
