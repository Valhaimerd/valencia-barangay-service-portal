<?php

return [

    'pilot_barangays' => [
        'Poblacion',
        'Lumbo',
        'Batangan',
        'Bagontaas',
        'Banlag',
        'Lurogan',
        'Tongantongan',
        'Guinoyuran',
        'Lilingayon',
        'Sinayawan',
    ],

    'portal_paths' => [
        'public' => '/',
        'resident' => '/resident',
        'barangay' => '/barangay',
        'super_admin' => '/super-admin',
    ],

    'user_roles' => [
        'resident' => 'Resident',
        'barangay_official' => 'Barangay Official',
        'city_super_admin' => 'City Super Admin',
    ],

    'official_roles' => [
        'barangay_admin' => 'Barangay Admin',
        'verifier' => 'Verifier',
        'encoder' => 'Encoder',
        'cashier' => 'Cashier',
        'release_officer' => 'Release Officer',
    ],

    'official_role_permissions' => [
        'barangay_admin' => [
            'reports',
            'verification_review',
            'request_processing',
            'payment_processing',
            'release_processing',
            'referral_processing',
        ],
        'verifier' => [
            'verification_review',
        ],
        'encoder' => [
            'request_processing',
            'referral_processing',
        ],
        'cashier' => [
            'payment_processing',
        ],
        'release_officer' => [
            'release_processing',
        ],
    ],

    'account_statuses' => [
        'active' => 'Active',
        'suspended' => 'Suspended',
        'deactivated' => 'Deactivated',
    ],

    'sex_options' => [
        'Male',
        'Female',
    ],

    'civil_status_options' => [
        'Single',
        'Married',
        'Widowed',
        'Separated',
    ],

    'identity_document_labels' => [
        'PhilSys ID',
        'Passport',
        'Driver\'s License',
        'UMID',
        'Voter\'s ID',
        'Senior Citizen ID',
        'PWD ID',
        'Postal ID',
        'School ID',
        'Company ID',
        'Barangay ID',
        'Other',
    ],

    'proof_of_residency_labels' => [
        'Utility Bill',
        'Lease Contract',
        'Voter Registration Record',
        'Barangay Certification',
        'Other',
    ],

    'supported_services' => [
        'barangay_clearance' => 'Barangay Clearance',
        'certificate_of_residency' => 'Certificate of Residency',
        'certificate_of_indigency' => 'Certificate of Indigency',
        'first_time_jobseeker_certification' => 'First-Time Jobseeker Certification',
        'medical_assistance' => 'Medical Assistance',
        'educational_assistance' => 'Educational Assistance',
    ],

    'service_categories' => [
        'document' => 'Document',
        'assistance' => 'Assistance',
    ],

    'verification_methods' => [
        'government_id' => 'Government ID',
        'secondary_id_with_proof' => 'Secondary ID with Proof of Residency',
    ],

    'verification_file_types' => [
        'government_id' => 'Government ID',
        'secondary_id' => 'Secondary ID',
        'proof_of_residency' => 'Proof of Residency',
        'face_front' => 'Front Face Capture',
        'face_left' => 'Left Face Capture',
        'face_right' => 'Right Face Capture',
    ],

    'resident_verification_statuses' => [
        'pending_verification' => 'Pending Verification',
        'needs_correction' => 'Needs Correction',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
    ],

    'file_review_statuses' => [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
    ],

    'document_request_statuses' => [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'for_payment' => 'For Payment',
        'for_printing' => 'For Printing',
        'ready_for_pickup' => 'Ready for Pickup',
        'released' => 'Released',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ],

    'assistance_request_statuses' => [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'needs_additional_documents' => 'Needs Additional Documents',
        'for_assessment' => 'For Assessment',
        'approved' => 'Approved',
        'referred' => 'Referred',
        'ready_for_claim' => 'Ready for Claim',
        'released' => 'Released',
        'closed' => 'Closed',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ],

    'attachment_review_statuses' => [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
    ],

    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'void' => 'Void',
    ],

    'referral_statuses' => [
        'referred' => 'Referred',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

];
