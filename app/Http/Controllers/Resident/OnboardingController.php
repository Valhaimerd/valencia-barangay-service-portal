<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\ResidentProfile;
use App\Models\ResidentVerification;
use App\Models\ResidentVerificationFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        $verification = $user->residentProfile?->verification;

        if ($verification) {
            return $this->redirectByVerificationStatus($verification->status);
        }

        return view('resident.onboarding.create', [
            'user' => $user,
            'barangays' => Barangay::query()->where('is_active', true)->orderBy('name')->get(),
            'residentProfile' => null,
            'verification' => null,
            'existingFiles' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        $user = $request->user();

        DB::transaction(function () use ($request, $validated, $user): void {
            $profilePhotoPath = $request->hasFile('profile_photo')
                ? $request->file('profile_photo')->store('resident/profile-photos', 'public')
                : null;

            $residentProfile = ResidentProfile::create([
                'user_id' => $user->id,
                'barangay_id' => $validated['barangay_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'sex' => $validated['sex'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'],
                'civil_status' => $validated['civil_status'],
                'mobile_number' => $validated['mobile_number'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'citizenship' => $validated['citizenship'] ?? 'Filipino',
                'current_address_line' => $validated['current_address_line'],
                'current_municipality' => 'Valencia City',
                'current_province' => 'Bukidnon',
                'permanent_address_line' => $validated['permanent_address_line'] ?? null,
                'permanent_barangay_name' => $validated['permanent_barangay_name'] ?? null,
                'permanent_municipality' => $validated['permanent_municipality'] ?? null,
                'permanent_province' => $validated['permanent_province'] ?? null,
                'profile_photo_path' => $profilePhotoPath,
            ]);

            $verification = ResidentVerification::create([
                'resident_profile_id' => $residentProfile->id,
                'verification_method' => $validated['verification_method'],
                'identity_document_label' => $validated['identity_document_label'],
                'identity_document_number' => $validated['identity_document_number'] ?? null,
                'proof_of_residency_label' => $validated['proof_of_residency_label'] ?? null,
                'status' => 'pending_verification',
                'submitted_at' => now(),
            ]);

            $this->syncVerificationFiles($request, $verification, true);

            $user->update([
                'name' => $residentProfile->full_name,
                'role' => 'resident',
                'is_resident_verified' => false,
            ]);
        });

        return redirect()->route('resident.verification.pending');
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        $residentProfile = $user->residentProfile;
        $verification = $residentProfile?->verification;

        if (! $residentProfile || ! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        if ($verification->status !== 'needs_correction') {
            return $this->redirectByVerificationStatus($verification->status);
        }

        return view('resident.onboarding.edit', [
            'user' => $user,
            'barangays' => Barangay::query()->where('is_active', true)->orderBy('name')->get(),
            'residentProfile' => $residentProfile,
            'verification' => $verification,
            'existingFiles' => $verification->files->keyBy('file_type'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.verification.files',
        ]);

        $residentProfile = $user->residentProfile;
        $verification = $residentProfile?->verification;

        abort_if(! $residentProfile || ! $verification, 404);
        abort_unless($verification->status === 'needs_correction', 403);

        $validated = $request->validate($this->rules($request, $verification));

        DB::transaction(function () use ($request, $validated, $user, $residentProfile, $verification): void {
            $profileData = [
                'barangay_id' => $validated['barangay_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'suffix' => $validated['suffix'] ?? null,
                'sex' => $validated['sex'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'],
                'civil_status' => $validated['civil_status'],
                'mobile_number' => $validated['mobile_number'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'citizenship' => $validated['citizenship'] ?? 'Filipino',
                'current_address_line' => $validated['current_address_line'],
                'current_municipality' => 'Valencia City',
                'current_province' => 'Bukidnon',
                'permanent_address_line' => $validated['permanent_address_line'] ?? null,
                'permanent_barangay_name' => $validated['permanent_barangay_name'] ?? null,
                'permanent_municipality' => $validated['permanent_municipality'] ?? null,
                'permanent_province' => $validated['permanent_province'] ?? null,
            ];

            if ($request->hasFile('profile_photo')) {
                if ($residentProfile->profile_photo_path) {
                    Storage::disk('public')->delete($residentProfile->profile_photo_path);
                }

                $profileData['profile_photo_path'] = $request->file('profile_photo')->store('resident/profile-photos', 'public');
            }

            $residentProfile->update($profileData);

            $verification->update([
                'verification_method' => $validated['verification_method'],
                'identity_document_label' => $validated['identity_document_label'],
                'identity_document_number' => $validated['identity_document_number'] ?? null,
                'proof_of_residency_label' => $validated['proof_of_residency_label'] ?? null,
                'status' => 'pending_verification',
                'submitted_at' => now(),
                'reviewed_at' => null,
                'approved_at' => null,
                'reviewed_by_user_id' => null,
                'correction_notes' => null,
                'rejection_reason' => null,
            ]);

            $this->syncVerificationFiles($request, $verification, false);

            $user->update([
                'name' => $residentProfile->fresh()->full_name,
                'is_resident_verified' => false,
            ]);
        });

        return redirect()->route('resident.verification.pending');
    }

    public function pending(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        $verification = $user->residentProfile?->verification;

        if (! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        if ($verification->status === 'verified') {
            return redirect()->route('resident.dashboard');
        }

        if ($verification->status === 'needs_correction') {
            return redirect()->route('resident.verification.correction');
        }

        if ($verification->status === 'rejected') {
            return redirect()->route('resident.verification.rejected');
        }

        return view('resident.onboarding.pending', [
            'user' => $user,
            'residentProfile' => $user->residentProfile,
            'verification' => $verification,
            'files' => $verification->files,
        ]);
    }

    public function correction(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        $verification = $user->residentProfile?->verification;

        if (! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        if ($verification->status === 'verified') {
            return redirect()->route('resident.dashboard');
        }

        if ($verification->status === 'pending_verification') {
            return redirect()->route('resident.verification.pending');
        }

        if ($verification->status === 'rejected') {
            return redirect()->route('resident.verification.rejected');
        }

        return view('resident.onboarding.correction', [
            'user' => $user,
            'residentProfile' => $user->residentProfile,
            'verification' => $verification,
            'files' => $verification->files,
        ]);
    }

    public function rejected(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'residentProfile.barangay',
            'residentProfile.verification.files',
        ]);

        $verification = $user->residentProfile?->verification;

        if (! $verification) {
            return redirect()->route('resident.onboarding.create');
        }

        if ($verification->status === 'verified') {
            return redirect()->route('resident.dashboard');
        }

        if ($verification->status === 'pending_verification') {
            return redirect()->route('resident.verification.pending');
        }

        if ($verification->status === 'needs_correction') {
            return redirect()->route('resident.verification.correction');
        }

        return view('resident.onboarding.rejected', [
            'user' => $user,
            'residentProfile' => $user->residentProfile,
            'verification' => $verification,
            'files' => $verification->files,
        ]);
    }

    private function rules(Request $request, ?ResidentVerification $verification = null): array
    {
        $existingFiles = $verification?->files?->keyBy('file_type') ?? collect();

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],

            'sex' => ['required', Rule::in(config('portal.sex_options'))],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:255'],
            'civil_status' => ['required', Rule::in(config('portal.civil_status_options'))],
            'mobile_number' => ['nullable', 'string', 'max:30'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'citizenship' => ['nullable', 'string', 'max:255'],

            'barangay_id' => ['required', 'exists:barangays,id'],
            'current_address_line' => ['required', 'string', 'max:1000'],
            'permanent_address_line' => ['nullable', 'string', 'max:1000'],
            'permanent_barangay_name' => ['nullable', 'string', 'max:255'],
            'permanent_municipality' => ['nullable', 'string', 'max:255'],
            'permanent_province' => ['nullable', 'string', 'max:255'],

            'verification_method' => ['required', Rule::in(array_keys(config('portal.verification_methods')))],
            'identity_document_label' => ['required', 'string', 'max:255'],
            'identity_document_number' => ['nullable', 'string', 'max:255'],
            'proof_of_residency_label' => [
                Rule::requiredIf(fn () => $request->input('verification_method') === 'secondary_id_with_proof'),
                'nullable',
                'string',
                'max:255',
            ],

            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'government_id_file' => [
                Rule::requiredIf(fn () => $request->input('verification_method') === 'government_id' && ! $existingFiles->has('government_id')),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'secondary_id_file' => [
                Rule::requiredIf(fn () => $request->input('verification_method') === 'secondary_id_with_proof' && ! $existingFiles->has('secondary_id')),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'proof_of_residency_file' => [
                Rule::requiredIf(fn () => $request->input('verification_method') === 'secondary_id_with_proof' && ! $existingFiles->has('proof_of_residency')),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],

            'face_front' => [
                Rule::requiredIf(fn () => ! $existingFiles->has('face_front')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'face_left' => [
                Rule::requiredIf(fn () => ! $existingFiles->has('face_left')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'face_right' => [
                Rule::requiredIf(fn () => ! $existingFiles->has('face_right')),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    private function syncVerificationFiles(Request $request, ResidentVerification $verification, bool $isNew): void
    {
        if ($request->input('verification_method') === 'government_id') {
            $this->deleteVerificationFilesByTypes($verification, ['secondary_id', 'proof_of_residency']);
        }

        if ($request->input('verification_method') === 'secondary_id_with_proof') {
            $this->deleteVerificationFilesByTypes($verification, ['government_id']);
        }

        $fileMap = [
            'government_id_file' => 'government_id',
            'secondary_id_file' => 'secondary_id',
            'proof_of_residency_file' => 'proof_of_residency',
            'face_front' => 'face_front',
            'face_left' => 'face_left',
            'face_right' => 'face_right',
        ];

        foreach ($fileMap as $inputName => $fileType) {
            if ($request->hasFile($inputName)) {
                $this->storeOrReplaceVerificationFile(
                    $verification,
                    $fileType,
                    $request->file($inputName)
                );
            }
        }
    }

    private function storeOrReplaceVerificationFile(ResidentVerification $verification, string $fileType, UploadedFile $file): void
    {
        $existing = $verification->files()->where('file_type', $fileType)->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->file_path);
            $existing->delete();
        }

        $path = $file->store("resident/verifications/{$verification->id}", 'public');

        ResidentVerificationFile::create([
            'resident_verification_id' => $verification->id,
            'file_type' => $fileType,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'review_status' => 'pending',
        ]);
    }

    private function deleteVerificationFilesByTypes(ResidentVerification $verification, array $types): void
    {
        $files = $verification->files()->whereIn('file_type', $types)->get();

        foreach ($files as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }
    }

    private function redirectByVerificationStatus(string $status): RedirectResponse
    {
        return match ($status) {
            'verified' => redirect()->route('resident.dashboard'),
            'pending_verification' => redirect()->route('resident.verification.pending'),
            'needs_correction' => redirect()->route('resident.verification.correction'),
            'rejected' => redirect()->route('resident.verification.rejected'),
            default => redirect()->route('resident.onboarding.create'),
        };
    }
}
