<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\OfficialProfile;
use App\Models\User;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class OfficialManagementController extends Controller
{
    public function index(): View
    {
        $officials = OfficialProfile::query()
            ->with(['user', 'barangay', 'assignedBy'])
            ->latest('created_at')
            ->paginate(10);

        return view('super-admin.officials.index', [
            'officials' => $officials,
        ]);
    }

    public function create(): View
    {
        return view('super-admin.officials.create', [
            'barangays' => Barangay::query()->where('is_active', true)->orderBy('name')->get(),
            'official' => new OfficialProfile(),
            'userAccount' => new User(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::transaction(function () use ($validated, $request): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'barangay_official',
                'account_status' => $validated['account_status'],
                'is_resident_verified' => false,
                'email_verified_at' => now(),
            ]);

            $officialProfile = OfficialProfile::create([
                'user_id' => $user->id,
                'barangay_id' => $validated['barangay_id'],
                'official_role' => $validated['official_role'],
                'employee_code' => $validated['employee_code'] ?? null,
                'assigned_by_user_id' => $request->user()->id,
                'assigned_at' => now(),
                'is_active' => $validated['account_status'] === 'active',
            ]);

            AuditTrail::record(
                user: $request->user(),
                action: 'official_account_created',
                subject: $officialProfile,
                description: 'Created barangay official account.',
                newValues: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'barangay_id' => $officialProfile->barangay_id,
                    'official_role' => $officialProfile->official_role,
                    'account_status' => $user->account_status,
                ],
                request: $request,
            );
        });

        return redirect()
            ->route('super_admin.officials.index')
            ->with('success', 'Official account created successfully.');
    }

    public function edit(OfficialProfile $official): View
    {
        $official->load(['user', 'barangay', 'assignedBy']);

        return view('super-admin.officials.edit', [
            'official' => $official,
            'userAccount' => $official->user,
            'barangays' => Barangay::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, OfficialProfile $official): RedirectResponse
    {
        $official->load('user');

        $validated = $request->validate($this->rules($official));

        $oldValues = [
            'name' => $official->user->name,
            'email' => $official->user->email,
            'barangay_id' => $official->barangay_id,
            'official_role' => $official->official_role,
            'account_status' => $official->user->account_status,
        ];

        DB::transaction(function () use ($validated, $official, $request, $oldValues): void {
            $official->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'account_status' => $validated['account_status'],
            ]);

            if (! empty($validated['password'])) {
                $official->user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $official->update([
                'barangay_id' => $validated['barangay_id'],
                'official_role' => $validated['official_role'],
                'employee_code' => $validated['employee_code'] ?? null,
                'is_active' => $validated['account_status'] === 'active',
            ]);

            AuditTrail::record(
                user: $request->user(),
                action: 'official_account_updated',
                subject: $official,
                description: 'Updated barangay official account.',
                oldValues: $oldValues,
                newValues: [
                    'name' => $official->user->name,
                    'email' => $official->user->email,
                    'barangay_id' => $official->barangay_id,
                    'official_role' => $official->official_role,
                    'account_status' => $official->user->account_status,
                ],
                request: $request,
            );
        });

        return redirect()
            ->route('super_admin.officials.index')
            ->with('success', 'Official account updated successfully.');
    }

    private function rules(?OfficialProfile $official = null): array
    {
        $userId = $official?->user_id;
        $officialId = $official?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $official
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
            'barangay_id' => ['required', 'exists:barangays,id'],
            'official_role' => ['required', Rule::in(array_keys(config('portal.official_roles')))],
            'employee_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('official_profiles', 'employee_code')->ignore($officialId),
            ],
            'account_status' => ['required', Rule::in(array_keys(config('portal.account_statuses')))],
        ];
    }
}
