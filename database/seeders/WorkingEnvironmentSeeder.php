<?php

namespace Database\Seeders;

use App\Models\AssistanceRequestDetail;
use App\Models\Barangay;
use App\Models\DocumentRequestDetail;
use App\Models\GeneratedDocument;
use App\Models\OfficialProfile;
use App\Models\PaymentRecord;
use App\Models\ReferralRecord;
use App\Models\ReleaseRecord;
use App\Models\RequestAttachment;
use App\Models\RequestStatusLog;
use App\Models\ResidentProfile;
use App\Models\ResidentVerification;
use App\Models\ResidentVerificationFile;
use App\Models\ServiceRequest;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkingEnvironmentSeeder extends Seeder
{
    private int $requestCounter = 1;
    private int $documentCounter = 1;
    private int $receiptCounter = 1;

    private array $documentPurposes = [
        'Local employment requirement',
        'School scholarship submission',
        'Utility connection requirement',
        'Business permit support document',
        'Police clearance companion requirement',
        'Bank account opening requirement',
        'Travel-related local documentation',
        'Government transaction support',
        'Internship application requirement',
        'Community records update',
    ];

    private array $medicalSummaries = [
        'Requesting support for maintenance medicine and follow-up consultation.',
        'Requesting medical aid for laboratory tests and prescribed medication.',
        'Household is experiencing financial difficulty due to hospitalization expenses.',
        'Requesting assistance for outpatient treatment and medicine refill.',
        'Beneficiary needs support for diagnostic procedures and transport to hospital.',
    ];

    private array $educationalSummaries = [
        'Requesting assistance for school fees, projects, and transportation.',
        'Student needs support for enrollment, uniforms, and basic school supplies.',
        'Household income is insufficient for current semester educational expenses.',
        'Requesting aid for tuition-related obligations and academic materials.',
        'Student is at risk of stopping studies due to financial hardship.',
    ];

    private array $referralDestinations = [
        'CSWDO Valencia City',
        'City Health Office',
        'PESO Valencia City',
        'Provincial Social Welfare Office',
        'Public Employment Referral Desk',
        'Hospital Social Service Unit',
    ];

    public function run(): void
    {
        $superAdmin = User::query()
            ->where('email', 'superadmin@valencia-portal.test')
            ->firstOrFail();

        $serviceTypes = ServiceType::query()
            ->whereIn('code', [
                'barangay_clearance',
                'certificate_of_residency',
                'certificate_of_indigency',
                'first_time_jobseeker_certification',
                'medical_assistance',
                'educational_assistance',
            ])
            ->get()
            ->keyBy('code');

        foreach (['Poblacion', 'Lumbo', 'Bagontaas'] as $barangayName) {
            $barangay = Barangay::query()->where('name', $barangayName)->firstOrFail();
            $slug = Str::slug($barangay->name);

            $this->purgePreviousDemoResidents($slug);

            $team = $this->seedOfficeTeam($barangay, $superAdmin);

            $verifiedResidents = $this->seedResidentBatch(
                barangay: $barangay,
                team: $team,
                verificationStatus: 'verified',
                startNumber: 1,
                count: 20
            );

            $this->seedResidentBatch(
                barangay: $barangay,
                team: $team,
                verificationStatus: 'pending_verification',
                startNumber: 21,
                count: 6
            );

            $this->seedResidentBatch(
                barangay: $barangay,
                team: $team,
                verificationStatus: 'needs_correction',
                startNumber: 27,
                count: 3
            );

            $this->seedResidentBatch(
                barangay: $barangay,
                team: $team,
                verificationStatus: 'rejected',
                startNumber: 30,
                count: 2
            );

            foreach ($verifiedResidents as $index => $residentProfile) {
                $documentCount = $index < 10 ? 3 : 2;

                for ($i = 0; $i < $documentCount; $i++) {
                    $this->createDocumentRequest(
                        residentProfile: $residentProfile,
                        barangay: $barangay,
                        team: $team,
                        serviceTypes: $serviceTypes
                    );
                }

                $shouldCreateAssistance = $index < 12 || fake()->boolean(40);

                if ($shouldCreateAssistance) {
                    $this->createAssistanceRequest(
                        residentProfile: $residentProfile,
                        barangay: $barangay,
                        team: $team,
                        serviceTypes: $serviceTypes
                    );
                }
            }
        }
    }

    private function purgePreviousDemoResidents(string $slug): void
    {
        Storage::disk('public')->deleteDirectory("demo/{$slug}");

        User::query()
            ->where('email', 'like', "{$slug}.resident%@valencia-portal.test")
            ->get()
            ->each(function (User $user): void {
                $user->delete();
            });
    }

    private function seedOfficeTeam(Barangay $barangay, User $superAdmin): array
    {
        $slug = Str::slug($barangay->name);

        $accounts = [
            'barangay_admin' => [
                'email' => "admin.{$slug}@valencia-portal.test",
                'name' => "{$barangay->name} Barangay Admin",
                'employee_code' => strtoupper($slug) . '-ADMIN-01',
            ],
            'verifier' => [
                'email' => "verifier.{$slug}@valencia-portal.test",
                'name' => "{$barangay->name} Verifier",
                'employee_code' => strtoupper($slug) . '-VERIFY-01',
            ],
            'encoder' => [
                'email' => "encoder.{$slug}@valencia-portal.test",
                'name' => "{$barangay->name} Encoder",
                'employee_code' => strtoupper($slug) . '-ENCODE-01',
            ],
            'cashier' => [
                'email' => "cashier.{$slug}@valencia-portal.test",
                'name' => "{$barangay->name} Cashier",
                'employee_code' => strtoupper($slug) . '-CASH-01',
            ],
            'release_officer' => [
                'email' => "release.{$slug}@valencia-portal.test",
                'name' => "{$barangay->name} Release Officer",
                'employee_code' => strtoupper($slug) . '-RELEASE-01',
            ],
        ];

        $team = [];

        foreach ($accounts as $officialRole => $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('Office12345!'),
                    'role' => 'barangay_official',
                    'account_status' => 'active',
                    'is_resident_verified' => false,
                    'email_verified_at' => now(),
                ]
            );

            OfficialProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'barangay_id' => $barangay->id,
                    'official_role' => $officialRole,
                    'employee_code' => $account['employee_code'],
                    'assigned_by_user_id' => $superAdmin->id,
                    'assigned_at' => now(),
                    'is_active' => true,
                ]
            );

            $team[$officialRole] = $user;
        }

        return $team;
    }

    private function seedResidentBatch(
        Barangay $barangay,
        array $team,
        string $verificationStatus,
        int $startNumber,
        int $count
    ): array {
        $profiles = [];
        $slug = Str::slug($barangay->name);

        for ($i = 0; $i < $count; $i++) {
            $sequence = $startNumber + $i;
            $residentCode = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

            $sex = fake()->randomElement(['Male', 'Female']);
            $firstName = $sex === 'Male' ? fake()->firstNameMale() : fake()->firstNameFemale();
            $middleName = fake()->boolean(65) ? fake()->firstName() : null;
            $lastName = fake()->lastName();
            $suffix = fake()->boolean(12) ? fake()->randomElement(['Jr.', 'Sr.', 'III']) : null;

            $fullName = trim(collect([$firstName, $middleName, $lastName, $suffix])->filter()->implode(' '));
            $email = "{$slug}.resident{$residentCode}@valencia-portal.test";

            $user = User::query()->create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('Resident12345!'),
                'role' => 'resident',
                'account_status' => 'active',
                'is_resident_verified' => $verificationStatus === 'verified',
                'email_verified_at' => now(),
            ]);

            $birthDate = now()->subYears(random_int(19, 58))->subDays(random_int(0, 364))->toDateString();

            $residentProfile = ResidentProfile::query()->create([
                'user_id' => $user->id,
                'barangay_id' => $barangay->id,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => $suffix,
                'sex' => $sex,
                'birth_date' => $birthDate,
                'birth_place' => fake()->randomElement(['Valencia City', 'Malaybalay City', 'Don Carlos', 'Quezon, Bukidnon']),
                'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed', 'Separated']),
                'mobile_number' => '09' . random_int(100000000, 999999999),
                'occupation' => fake()->randomElement([
                    'Student',
                    'Farmer',
                    'Driver',
                    'Vendor',
                    'Teacher',
                    'Household Helper',
                    'Construction Worker',
                    'Self-Employed',
                    'Office Staff',
                    'Unemployed',
                ]),
                'citizenship' => 'Filipino',
                'current_address_line' => random_int(1, 300) . ' Purok ' . random_int(1, 9) . ', ' . $barangay->name,
                'current_municipality' => 'Valencia City',
                'current_province' => 'Bukidnon',
                'permanent_address_line' => random_int(1, 300) . ' Purok ' . random_int(1, 9) . ', ' . $barangay->name,
                'permanent_barangay_name' => $barangay->name,
                'permanent_municipality' => 'Valencia City',
                'permanent_province' => 'Bukidnon',
                'profile_photo_path' => null,
            ]);

            $verificationMethod = fake()->randomElement(['government_id', 'secondary_id_with_proof']);
            $submittedAt = now()->subDays(random_int(5, 70))->subHours(random_int(1, 20));

            $reviewedAt = null;
            $approvedAt = null;
            $reviewedBy = null;
            $correctionNotes = null;
            $rejectionReason = null;

            if ($verificationStatus !== 'pending_verification') {
                $reviewedAt = (clone $submittedAt)->addDays(random_int(1, 5))->addHours(random_int(1, 8));
                $reviewedBy = $team['verifier']->id;

                if ($verificationStatus === 'verified') {
                    $approvedAt = $reviewedAt;
                } elseif ($verificationStatus === 'needs_correction') {
                    $correctionNotes = fake()->randomElement([
                        'Please re-upload a clearer identification image.',
                        'Proof of residency is blurred. Please submit a clearer file.',
                        'Face capture does not match the uploaded identification well enough.',
                    ]);
                } elseif ($verificationStatus === 'rejected') {
                    $rejectionReason = fake()->randomElement([
                        'Submitted identity information could not be validated.',
                        'Proof of residency was insufficient for verification.',
                        'Identity capture and profile information were inconsistent.',
                    ]);
                }
            }

            $verification = ResidentVerification::query()->create([
                'resident_profile_id' => $residentProfile->id,
                'verification_method' => $verificationMethod,
                'identity_document_label' => $verificationMethod === 'government_id'
                    ? fake()->randomElement(['PhilSys ID', 'Driver\'s License', 'Passport', 'Voter\'s ID'])
                    : fake()->randomElement(['School ID', 'Company ID', 'Barangay ID', 'Other']),
                'identity_document_number' => strtoupper(Str::random(10)),
                'proof_of_residency_label' => $verificationMethod === 'secondary_id_with_proof'
                    ? fake()->randomElement(['Utility Bill', 'Lease Contract', 'Voter Registration Record', 'Barangay Certification'])
                    : null,
                'status' => $verificationStatus,
                'submitted_at' => $submittedAt,
                'reviewed_at' => $reviewedAt,
                'approved_at' => $approvedAt,
                'reviewed_by_user_id' => $reviewedBy,
                'correction_notes' => $correctionNotes,
                'rejection_reason' => $rejectionReason,
            ]);

            $this->createVerificationFiles(
                verification: $verification,
                verificationStatus: $verificationStatus,
                method: $verificationMethod,
                slug: $slug,
                residentCode: $residentCode
            );

            if ($verificationStatus === 'verified') {
                $profiles[] = $residentProfile;
            }
        }

        return $profiles;
    }

    private function createVerificationFiles(
        ResidentVerification $verification,
        string $verificationStatus,
        string $method,
        string $slug,
        string $residentCode
    ): void {
        $fileTypes = $method === 'government_id'
            ? ['government_id', 'face_front', 'face_left', 'face_right']
            : ['secondary_id', 'proof_of_residency', 'face_front', 'face_left', 'face_right'];

        foreach ($fileTypes as $index => $fileType) {
            $reviewStatus = match ($verificationStatus) {
                'verified' => 'accepted',
                'pending_verification' => 'pending',
                'needs_correction' => $index === 0 || ($fileType === 'proof_of_residency') ? 'rejected' : 'accepted',
                'rejected' => 'rejected',
                default => 'pending',
            };

            $filePath = "demo/{$slug}/verification_files/resident_{$residentCode}_{$fileType}.txt";

            Storage::disk('public')->put(
                $filePath,
                "Demo verification file for {$fileType}.\nResident verification ID: {$verification->id}\nStatus: {$verificationStatus}\n"
            );

            ResidentVerificationFile::query()->create([
                'resident_verification_id' => $verification->id,
                'file_type' => $fileType,
                'file_path' => $filePath,
                'original_name' => basename($filePath),
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('public')->size($filePath),
                'review_status' => $reviewStatus,
                'reviewer_notes' => $reviewStatus === 'rejected'
                    ? 'Demo flagged file for review workflow.'
                    : null,
            ]);
        }
    }

    private function createDocumentRequest(
        ResidentProfile $residentProfile,
        Barangay $barangay,
        array $team,
        $serviceTypes
    ): void {
        $serviceCode = fake()->randomElement([
            'barangay_clearance',
            'certificate_of_residency',
            'certificate_of_indigency',
            'first_time_jobseeker_certification',
        ]);

        /** @var ServiceType $serviceType */
        $serviceType = $serviceTypes[$serviceCode];

        $status = $this->weightedPick(
            $serviceType->requires_payment
                ? [
                    'submitted' => 3,
                    'under_review' => 5,
                    'approved' => 5,
                    'for_payment' => 6,
                    'for_printing' => 5,
                    'ready_for_pickup' => 5,
                    'released' => 6,
                    'rejected' => 2,
                    'cancelled' => 1,
                ]
                : [
                    'submitted' => 3,
                    'under_review' => 5,
                    'approved' => 5,
                    'for_printing' => 5,
                    'ready_for_pickup' => 5,
                    'released' => 6,
                    'rejected' => 2,
                    'cancelled' => 1,
                ]
        );

        $submittedAt = now()->subDays(random_int(1, 80))->subHours(random_int(1, 20));
        $referenceNumber = $this->nextReferenceNumber($barangay, $serviceType->code);

        $request = ServiceRequest::query()->create([
            'reference_number' => $referenceNumber,
            'resident_profile_id' => $residentProfile->id,
            'service_type_id' => $serviceType->id,
            'barangay_id' => $barangay->id,
            'request_category' => 'document',
            'current_status' => $status,
            'submitted_at' => $submittedAt,
            'latest_status_at' => $submittedAt,
            'assigned_to_user_id' => in_array($status, ['submitted', 'cancelled'], true) ? null : $team['encoder']->id,
            'reviewed_by_user_id' => in_array($status, ['under_review', 'approved', 'for_payment', 'for_printing', 'ready_for_pickup', 'released', 'rejected'], true)
                ? $team['encoder']->id
                : null,
            'approved_by_user_id' => in_array($status, ['approved', 'for_payment', 'for_printing', 'ready_for_pickup', 'released'], true)
                ? $team['barangay_admin']->id
                : null,
            'rejected_by_user_id' => $status === 'rejected' ? $team['barangay_admin']->id : null,
            'cancelled_by_user_id' => $status === 'cancelled' ? $residentProfile->user_id : null,
            'rejection_reason' => $status === 'rejected' ? 'Request requirements were incomplete or inconsistent.' : null,
            'cancellation_reason' => $status === 'cancelled' ? 'Resident cancelled the request before completion.' : null,
            'internal_notes' => fake()->boolean(40) ? 'Demo seeded request for workflow testing.' : null,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);

        $timeline = $this->documentTimeline($status, $serviceType->requires_payment);
        $logData = $this->createStatusLogs(
            request: $request,
            timeline: $timeline,
            category: 'document',
            residentProfile: $residentProfile,
            team: $team,
            submittedAt: $submittedAt
        );

        $timestamps = $logData['timestamps'];
        $latestStatusAt = $logData['last'];

        $receiptNumber = null;
        if ($serviceType->requires_payment && in_array($status, ['for_printing', 'ready_for_pickup', 'released'], true)) {
            $receiptNumber = $this->nextOfficialReceiptNumber($barangay);

            PaymentRecord::query()->create([
                'request_id' => $request->id,
                'amount' => $this->paymentAmountForService($serviceCode),
                'payment_status' => 'paid',
                'official_receipt_number' => $receiptNumber,
                'paid_at' => $timestamps['for_payment'] ?? $latestStatusAt,
                'received_by_user_id' => $team['cashier']->id,
                'notes' => 'Demo paid transaction.',
            ]);
        } elseif ($serviceType->requires_payment && $status === 'for_payment') {
            PaymentRecord::query()->create([
                'request_id' => $request->id,
                'amount' => $this->paymentAmountForService($serviceCode),
                'payment_status' => 'pending',
                'official_receipt_number' => null,
                'paid_at' => null,
                'received_by_user_id' => null,
                'notes' => 'Awaiting payment at cashier window.',
            ]);
        }

        DocumentRequestDetail::query()->create([
            'request_id' => $request->id,
            'purpose' => fake()->randomElement($this->documentPurposes),
            'cedula_number' => fake()->boolean(85) ? strtoupper(Str::random(8)) : null,
            'cedula_date' => now()->subMonths(random_int(1, 8))->toDateString(),
            'cedula_place' => 'Valencia City, Bukidnon',
            'years_of_residency' => random_int(1, 18),
            'months_of_residency' => random_int(0, 11),
            'jobseeker_availment_count' => $serviceCode === 'first_time_jobseeker_certification' ? 0 : 0,
            'oath_required' => $serviceCode === 'first_time_jobseeker_certification',
            'payment_amount' => $serviceType->requires_payment ? $this->paymentAmountForService($serviceCode) : null,
            'official_receipt_number' => $receiptNumber,
            'prepared_by_user_id' => in_array($status, ['for_printing', 'ready_for_pickup', 'released'], true) ? $team['encoder']->id : null,
            'printed_at' => $timestamps['for_printing'] ?? null,
        ]);

        $this->createDocumentAttachments(
            request: $request,
            residentProfile: $residentProfile,
            slug: Str::slug($barangay->name),
            status: $status
        );

        if (in_array($status, ['for_printing', 'ready_for_pickup', 'released'], true)) {
            $documentNumber = $this->nextDocumentNumber($barangay);
            $generatedFilePath = "demo/" . Str::slug($barangay->name) . "/generated_documents/{$documentNumber}.txt";

            Storage::disk('public')->put(
                $generatedFilePath,
                "Demo generated document for request {$request->reference_number}\nDocument number: {$documentNumber}\n"
            );

            GeneratedDocument::query()->create([
                'request_id' => $request->id,
                'document_number' => $documentNumber,
                'file_path' => $generatedFilePath,
                'generated_at' => $timestamps['for_printing'] ?? $latestStatusAt,
                'prepared_by_user_id' => $team['encoder']->id,
                'printed_at' => $timestamps['for_printing'] ?? null,
                'printed_by_user_id' => in_array($status, ['for_printing', 'ready_for_pickup', 'released'], true) ? $team['encoder']->id : null,
            ]);
        }

        if ($status === 'released') {
            ReleaseRecord::query()->create([
                'request_id' => $request->id,
                'released_to_name' => $residentProfile->full_name,
                'released_to_relationship' => 'Self',
                'released_at' => $timestamps['released'] ?? $latestStatusAt,
                'released_by_user_id' => $team['release_officer']->id,
                'claimant_identification_notes' => 'Presented demo government-issued ID during release.',
                'remarks' => 'Demo release completed successfully.',
            ]);
        }

        $request->update([
            'latest_status_at' => $latestStatusAt,
            'completed_at' => $status === 'released' ? ($timestamps['released'] ?? $latestStatusAt) : null,
            'cancelled_at' => $status === 'cancelled' ? ($timestamps['cancelled'] ?? $latestStatusAt) : null,
        ]);
    }

    private function createAssistanceRequest(
        ResidentProfile $residentProfile,
        Barangay $barangay,
        array $team,
        $serviceTypes
    ): void {
        $serviceCode = fake()->randomElement([
            'medical_assistance',
            'educational_assistance',
        ]);

        /** @var ServiceType $serviceType */
        $serviceType = $serviceTypes[$serviceCode];

        $status = $this->weightedPick([
            'submitted' => 3,
            'under_review' => 4,
            'needs_additional_documents' => 3,
            'for_assessment' => 4,
            'approved' => 5,
            'referred' => 3,
            'ready_for_claim' => 3,
            'released' => 4,
            'closed' => 3,
            'rejected' => 2,
            'cancelled' => 1,
        ]);

        $submittedAt = now()->subDays(random_int(1, 80))->subHours(random_int(1, 20));
        $referenceNumber = $this->nextReferenceNumber($barangay, $serviceType->code);

        $request = ServiceRequest::query()->create([
            'reference_number' => $referenceNumber,
            'resident_profile_id' => $residentProfile->id,
            'service_type_id' => $serviceType->id,
            'barangay_id' => $barangay->id,
            'request_category' => 'assistance',
            'current_status' => $status,
            'submitted_at' => $submittedAt,
            'latest_status_at' => $submittedAt,
            'assigned_to_user_id' => in_array($status, ['submitted', 'cancelled'], true) ? null : $team['encoder']->id,
            'reviewed_by_user_id' => in_array($status, [
                'under_review',
                'needs_additional_documents',
                'for_assessment',
                'approved',
                'referred',
                'ready_for_claim',
                'released',
                'closed',
                'rejected',
            ], true) ? $team['encoder']->id : null,
            'approved_by_user_id' => in_array($status, ['approved', 'referred', 'ready_for_claim', 'released', 'closed'], true)
                ? $team['barangay_admin']->id
                : null,
            'rejected_by_user_id' => $status === 'rejected' ? $team['barangay_admin']->id : null,
            'cancelled_by_user_id' => $status === 'cancelled' ? $residentProfile->user_id : null,
            'rejection_reason' => $status === 'rejected' ? 'Resident did not meet current documentary or assessment requirements.' : null,
            'cancellation_reason' => $status === 'cancelled' ? 'Resident withdrew the assistance request.' : null,
            'internal_notes' => fake()->boolean(45) ? 'Demo assistance workflow item.' : null,
            'completed_at' => null,
            'cancelled_at' => null,
        ]);

        $timeline = $this->assistanceTimeline($status);
        $logData = $this->createStatusLogs(
            request: $request,
            timeline: $timeline,
            category: 'assistance',
            residentProfile: $residentProfile,
            team: $team,
            submittedAt: $submittedAt
        );

        $timestamps = $logData['timestamps'];
        $latestStatusAt = $logData['last'];
        $requestedAmount = random_int(3000, 15000);

        AssistanceRequestDetail::query()->create([
            'request_id' => $request->id,
            'case_summary' => $serviceCode === 'medical_assistance'
                ? fake()->randomElement($this->medicalSummaries)
                : fake()->randomElement($this->educationalSummaries),
            'requested_amount' => $requestedAmount,
            'assessment_notes' => isset($timestamps['for_assessment'])
                ? 'Initial social case assessment completed for demo environment.'
                : null,
            'assessment_date' => isset($timestamps['for_assessment'])
                ? $timestamps['for_assessment']->toDateString()
                : null,
            'claimant_name' => $residentProfile->full_name,
            'relationship_to_beneficiary' => 'Self',
            'referral_destination' => in_array($status, ['referred', 'ready_for_claim', 'released', 'closed'], true)
                ? fake()->randomElement($this->referralDestinations)
                : null,
        ]);

        $this->createAssistanceAttachments(
            request: $request,
            residentProfile: $residentProfile,
            slug: Str::slug($barangay->name),
            status: $status,
            serviceCode: $serviceCode
        );

        if (in_array($status, ['referred', 'ready_for_claim', 'released', 'closed'], true)) {
            ReferralRecord::query()->create([
                'request_id' => $request->id,
                'referred_to' => fake()->randomElement($this->referralDestinations),
                'referral_notes' => 'Demo referral endorsement created for assistance processing.',
                'referral_status' => in_array($status, ['released', 'closed'], true) ? 'completed' : 'referred',
                'referred_at' => $timestamps['referred'] ?? $latestStatusAt,
                'referred_by_user_id' => $team['encoder']->id,
            ]);
        }

        if (in_array($status, ['released', 'closed'], true)) {
            ReleaseRecord::query()->create([
                'request_id' => $request->id,
                'released_to_name' => $residentProfile->full_name,
                'released_to_relationship' => 'Self',
                'released_at' => $timestamps['released'] ?? $timestamps['closed'] ?? $latestStatusAt,
                'released_by_user_id' => $team['release_officer']->id,
                'claimant_identification_notes' => 'Demo claimant verification completed before release.',
                'remarks' => 'Demo assistance release completed.',
            ]);
        }

        $request->update([
            'latest_status_at' => $latestStatusAt,
            'completed_at' => in_array($status, ['released', 'closed'], true)
                ? ($timestamps['released'] ?? $timestamps['closed'] ?? $latestStatusAt)
                : null,
            'cancelled_at' => $status === 'cancelled' ? ($timestamps['cancelled'] ?? $latestStatusAt) : null,
        ]);
    }

    private function createDocumentAttachments(
        ServiceRequest $request,
        ResidentProfile $residentProfile,
        string $slug,
        string $status
    ): void {
        $attachmentTypes = [
            'supporting_id',
            'proof_of_residency',
        ];

        foreach ($attachmentTypes as $attachmentType) {
            $reviewStatus = match ($status) {
                'submitted', 'under_review', 'for_payment' => 'pending',
                'rejected' => $attachmentType === 'proof_of_residency' ? 'rejected' : 'accepted',
                'cancelled' => 'pending',
                default => 'accepted',
            };

            $filePath = "demo/{$slug}/request_attachments/{$request->reference_number}_{$attachmentType}.txt";

            Storage::disk('public')->put(
                $filePath,
                "Demo request attachment.\nRequest: {$request->reference_number}\nAttachment type: {$attachmentType}\n"
            );

            RequestAttachment::query()->create([
                'request_id' => $request->id,
                'attachment_type' => $attachmentType,
                'file_path' => $filePath,
                'original_name' => basename($filePath),
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('public')->size($filePath),
                'uploaded_by_user_id' => $residentProfile->user_id,
                'is_required' => true,
                'review_status' => $reviewStatus,
                'reviewer_notes' => $reviewStatus === 'rejected'
                    ? 'Demo rejected supporting file.'
                    : null,
            ]);
        }
    }

    private function createAssistanceAttachments(
        ServiceRequest $request,
        ResidentProfile $residentProfile,
        string $slug,
        string $status,
        string $serviceCode
    ): void {
        $attachmentTypes = $serviceCode === 'medical_assistance'
            ? ['medical_certificate', 'hospital_bill']
            : ['registration_form', 'grade_slip'];

        foreach ($attachmentTypes as $attachmentType) {
            $reviewStatus = match ($status) {
                'submitted', 'under_review', 'needs_additional_documents' => 'pending',
                'rejected' => $attachmentType === $attachmentTypes[0] ? 'rejected' : 'accepted',
                'cancelled' => 'pending',
                default => 'accepted',
            };

            $filePath = "demo/{$slug}/request_attachments/{$request->reference_number}_{$attachmentType}.txt";

            Storage::disk('public')->put(
                $filePath,
                "Demo assistance attachment.\nRequest: {$request->reference_number}\nAttachment type: {$attachmentType}\n"
            );

            RequestAttachment::query()->create([
                'request_id' => $request->id,
                'attachment_type' => $attachmentType,
                'file_path' => $filePath,
                'original_name' => basename($filePath),
                'mime_type' => 'text/plain',
                'file_size' => Storage::disk('public')->size($filePath),
                'uploaded_by_user_id' => $residentProfile->user_id,
                'is_required' => true,
                'review_status' => $reviewStatus,
                'reviewer_notes' => $reviewStatus === 'rejected'
                    ? 'Demo flagged assistance document.'
                    : null,
            ]);
        }
    }

    private function createStatusLogs(
        ServiceRequest $request,
        array $timeline,
        string $category,
        ResidentProfile $residentProfile,
        array $team,
        $submittedAt
    ): array {
        $cursor = (clone $submittedAt);
        $previousStatus = null;
        $timestamps = [];

        foreach ($timeline as $index => $status) {
            $actedAt = $index === 0
                ? (clone $submittedAt)
                : (clone $cursor)->addHours(random_int(6, 36));

            RequestStatusLog::query()->create([
                'request_id' => $request->id,
                'from_status' => $previousStatus,
                'to_status' => $status,
                'remarks' => $this->statusRemarks($category, $status),
                'acted_by_user_id' => $this->statusActorId($category, $status, $residentProfile, $team),
                'acted_at' => $actedAt,
            ]);

            $timestamps[$status] = $actedAt;
            $cursor = (clone $actedAt);
            $previousStatus = $status;
        }

        return [
            'last' => $cursor,
            'timestamps' => $timestamps,
        ];
    }

    private function documentTimeline(string $finalStatus, bool $requiresPayment): array
    {
        return match ($finalStatus) {
            'submitted' => ['submitted'],
            'under_review' => ['submitted', 'under_review'],
            'approved' => ['submitted', 'under_review', 'approved'],
            'for_payment' => ['submitted', 'under_review', 'approved', 'for_payment'],
            'for_printing' => $requiresPayment
                ? ['submitted', 'under_review', 'approved', 'for_payment', 'for_printing']
                : ['submitted', 'under_review', 'approved', 'for_printing'],
            'ready_for_pickup' => $requiresPayment
                ? ['submitted', 'under_review', 'approved', 'for_payment', 'for_printing', 'ready_for_pickup']
                : ['submitted', 'under_review', 'approved', 'for_printing', 'ready_for_pickup'],
            'released' => $requiresPayment
                ? ['submitted', 'under_review', 'approved', 'for_payment', 'for_printing', 'ready_for_pickup', 'released']
                : ['submitted', 'under_review', 'approved', 'for_printing', 'ready_for_pickup', 'released'],
            'rejected' => ['submitted', 'under_review', 'rejected'],
            'cancelled' => ['submitted', 'cancelled'],
            default => ['submitted'],
        };
    }

    private function assistanceTimeline(string $finalStatus): array
    {
        return match ($finalStatus) {
            'submitted' => ['submitted'],
            'under_review' => ['submitted', 'under_review'],
            'needs_additional_documents' => ['submitted', 'under_review', 'needs_additional_documents'],
            'for_assessment' => ['submitted', 'under_review', 'for_assessment'],
            'approved' => ['submitted', 'under_review', 'for_assessment', 'approved'],
            'referred' => ['submitted', 'under_review', 'for_assessment', 'approved', 'referred'],
            'ready_for_claim' => ['submitted', 'under_review', 'for_assessment', 'approved', 'referred', 'ready_for_claim'],
            'released' => ['submitted', 'under_review', 'for_assessment', 'approved', 'referred', 'ready_for_claim', 'released'],
            'closed' => ['submitted', 'under_review', 'for_assessment', 'approved', 'referred', 'ready_for_claim', 'released', 'closed'],
            'rejected' => ['submitted', 'under_review', 'rejected'],
            'cancelled' => ['submitted', 'cancelled'],
            default => ['submitted'],
        };
    }

    private function statusActorId(string $category, string $status, ResidentProfile $residentProfile, array $team): ?int
    {
        if ($category === 'document') {
            return match ($status) {
                'submitted', 'cancelled' => $residentProfile->user_id,
                'under_review', 'for_printing', 'ready_for_pickup' => $team['encoder']->id,
                'approved', 'rejected' => $team['barangay_admin']->id,
                'for_payment' => $team['cashier']->id,
                'released' => $team['release_officer']->id,
                default => $team['encoder']->id,
            };
        }

        return match ($status) {
            'submitted', 'cancelled' => $residentProfile->user_id,
            'under_review', 'needs_additional_documents', 'for_assessment', 'referred', 'ready_for_claim' => $team['encoder']->id,
            'approved', 'rejected', 'closed' => $team['barangay_admin']->id,
            'released' => $team['release_officer']->id,
            default => $team['encoder']->id,
        };
    }

    private function statusRemarks(string $category, string $status): string
    {
        if ($category === 'document') {
            return match ($status) {
                'submitted' => 'Resident submitted a document request.',
                'under_review' => 'Encoder started document review.',
                'approved' => 'Barangay admin approved the request.',
                'for_payment' => 'Request forwarded to cashier for payment.',
                'for_printing' => 'Document is queued for printing.',
                'ready_for_pickup' => 'Printed document is ready for pickup.',
                'released' => 'Document released to claimant.',
                'rejected' => 'Document request was rejected after review.',
                'cancelled' => 'Resident cancelled the document request.',
                default => 'Document workflow updated.',
            };
        }

        return match ($status) {
            'submitted' => 'Resident submitted an assistance request.',
            'under_review' => 'Assistance request is under review.',
            'needs_additional_documents' => 'Additional supporting files were requested.',
            'for_assessment' => 'Assistance case scheduled for assessment.',
            'approved' => 'Assistance request was approved.',
            'referred' => 'Request was referred to partner office/service desk.',
            'ready_for_claim' => 'Approved assistance is ready for claimant processing.',
            'released' => 'Assistance was released to claimant.',
            'closed' => 'Assistance request has been completed and closed.',
            'rejected' => 'Assistance request was rejected after evaluation.',
            'cancelled' => 'Resident cancelled the assistance request.',
            default => 'Assistance workflow updated.',
        };
    }

    private function weightedPick(array $weights): string
    {
        $pool = [];

        foreach ($weights as $value => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = $value;
            }
        }

        return fake()->randomElement($pool);
    }

    private function nextReferenceNumber(Barangay $barangay, string $serviceCode): string
    {
        $barangayCode = strtoupper(substr(Str::slug($barangay->name, ''), 0, 3));
        $serviceCodeShort = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $serviceCode), 0, 4));
        $number = str_pad((string) $this->requestCounter++, 5, '0', STR_PAD_LEFT);

        return 'REQ-' . now()->format('Ymd') . '-' . $barangayCode . '-' . $serviceCodeShort . '-' . $number;
    }

    private function nextDocumentNumber(Barangay $barangay): string
    {
        $barangayCode = strtoupper(substr(Str::slug($barangay->name, ''), 0, 3));
        $number = str_pad((string) $this->documentCounter++, 5, '0', STR_PAD_LEFT);

        return 'DOC-' . $barangayCode . '-' . now()->format('Y') . '-' . $number;
    }

    private function nextOfficialReceiptNumber(Barangay $barangay): string
    {
        $barangayCode = strtoupper(substr(Str::slug($barangay->name, ''), 0, 3));
        $number = str_pad((string) $this->receiptCounter++, 5, '0', STR_PAD_LEFT);

        return 'OR-' . $barangayCode . '-' . now()->format('Y') . '-' . $number;
    }

    private function paymentAmountForService(string $serviceCode): float
    {
        return match ($serviceCode) {
            'barangay_clearance' => 100.00,
            'certificate_of_residency' => 75.00,
            default => 0.00,
        };
    }
}
