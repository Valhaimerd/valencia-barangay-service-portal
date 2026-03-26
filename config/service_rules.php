<?php

return [

    'services' => [
        'barangay_clearance' => [
            'description' => 'Certificate request for general clearance transactions that require barangay certification and cedula details.',
            'fields' => [
                'purpose' => [
                    'label' => 'Purpose',
                    'type' => 'textarea',
                    'rows' => 5,
                    'target' => 'document',
                    'rules' => ['required', 'string', 'max:2000'],
                ],
                'cedula_number' => [
                    'label' => 'Cedula Number',
                    'type' => 'text',
                    'target' => 'document',
                    'rules' => ['required', 'string', 'max:255'],
                ],
                'cedula_date' => [
                    'label' => 'Cedula Date',
                    'type' => 'date',
                    'target' => 'document',
                    'rules' => ['required', 'date'],
                ],
                'cedula_place' => [
                    'label' => 'Cedula Place',
                    'type' => 'text',
                    'target' => 'document',
                    'rules' => ['required', 'string', 'max:255'],
                ],
            ],
            'attachments' => [
                'valid_id' => [
                    'label' => 'Valid ID',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'cedula_copy' => [
                    'label' => 'Cedula Copy',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'review_checklist' => [
                'Resident identity matches the verified resident profile.',
                'Cedula number, date, and place are complete and readable.',
                'Purpose is appropriate for barangay clearance issuance.',
            ],
        ],

        'certificate_of_residency' => [
            'description' => 'Certificate request proving current residency within the barangay, including declared residency period.',
            'fields' => [
                'purpose' => [
                    'label' => 'Purpose',
                    'type' => 'textarea',
                    'rows' => 5,
                    'target' => 'document',
                    'rules' => ['required', 'string', 'max:2000'],
                ],
                'years_of_residency' => [
                    'label' => 'Years of Residency',
                    'type' => 'number',
                    'min' => 0,
                    'target' => 'document',
                    'cast' => 'integer',
                    'rules' => ['required', 'integer', 'min:0', 'max:150'],
                ],
                'months_of_residency' => [
                    'label' => 'Months of Residency',
                    'type' => 'number',
                    'min' => 0,
                    'max' => 11,
                    'target' => 'document',
                    'cast' => 'integer',
                    'rules' => ['required', 'integer', 'min:0', 'max:11'],
                ],
            ],
            'attachments' => [
                'valid_id' => [
                    'label' => 'Valid ID',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'proof_of_residency' => [
                    'label' => 'Proof of Residency',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'review_checklist' => [
                'Resident address matches verified barangay residency.',
                'Declared years and months of residency are reasonable.',
                'Supporting residency proof is sufficient and readable.',
            ],
        ],

        'certificate_of_indigency' => [
            'description' => 'Certificate request for residents needing barangay indigency certification for aid, medical, school, or related use.',
            'fields' => [
                'purpose' => [
                    'label' => 'Purpose',
                    'type' => 'textarea',
                    'rows' => 5,
                    'target' => 'document',
                    'rules' => ['required', 'string', 'max:2000'],
                ],
            ],
            'attachments' => [
                'valid_id' => [
                    'label' => 'Valid ID',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'proof_of_need' => [
                    'label' => 'Proof of Need',
                    'required' => false,
                    'rules' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'review_checklist' => [
                'Resident identity is verified and current.',
                'Purpose aligns with indigency certification use.',
                'Any submitted supporting proof is sufficient for reviewer assessment.',
            ],
        ],

        'first_time_jobseeker_certification' => [
            'description' => 'One-time certificate request for first-time jobseekers covered by the First Time Jobseekers Assistance framework.',
            'fields' => [
                'purpose' => [
                    'label' => 'Purpose',
                    'type' => 'textarea',
                    'rows' => 5,
                    'target' => 'document',
                    'default' => 'First-time job application',
                    'rules' => ['required', 'string', 'max:2000'],
                ],
                'oath_required' => [
                    'label' => 'Include oath requirement',
                    'type' => 'checkbox',
                    'target' => 'document',
                    'cast' => 'boolean',
                    'default' => true,
                    'rules' => ['nullable', 'boolean'],
                ],
            ],
            'attachments' => [
                'valid_id' => [
                    'label' => 'Valid ID',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'supporting_identity_document' => [
                    'label' => 'Supporting Identity Document',
                    'required' => false,
                    'rules' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'document_defaults' => [
                'jobseeker_availment_count' => 1,
            ],
            'constraints' => [
                'single_active_request' => true,
            ],
            'review_checklist' => [
                'Resident has no other active first-time jobseeker request.',
                'Identity and residency are already verified.',
                'Jobseeker-specific declaration or oath handling is complete.',
            ],
        ],

        'medical_assistance' => [
            'description' => 'Case-based assistance request for medical needs, treatment, medicine, or hospitalization support.',
            'fields' => [
                'case_summary' => [
                    'label' => 'Case Summary',
                    'type' => 'textarea',
                    'rows' => 6,
                    'target' => 'assistance',
                    'rules' => ['required', 'string', 'max:5000'],
                ],
                'requested_amount' => [
                    'label' => 'Requested Amount',
                    'type' => 'number',
                    'min' => 0,
                    'step' => '0.01',
                    'target' => 'assistance',
                    'cast' => 'decimal',
                    'rules' => ['nullable', 'numeric', 'min:0'],
                ],
                'claimant_name' => [
                    'label' => 'Claimant Name',
                    'type' => 'text',
                    'target' => 'assistance',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
                'relationship_to_beneficiary' => [
                    'label' => 'Relationship to Beneficiary',
                    'type' => 'text',
                    'target' => 'assistance',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
            ],
            'attachments' => [
                'medical_certificate' => [
                    'label' => 'Medical Certificate',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'prescription_or_request' => [
                    'label' => 'Prescription or Medical Request',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'hospital_bill_or_quote' => [
                    'label' => 'Hospital Bill or Cost Estimate',
                    'required' => false,
                    'rules' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'review_checklist' => [
                'Medical case summary is clear and specific.',
                'Required medical documents are readable and relevant.',
                'Requested amount is consistent with the submitted case.',
            ],
        ],

        'educational_assistance' => [
            'description' => 'Case-based assistance request for school-related needs such as tuition, assessment, or academic support.',
            'fields' => [
                'case_summary' => [
                    'label' => 'Case Summary',
                    'type' => 'textarea',
                    'rows' => 6,
                    'target' => 'assistance',
                    'rules' => ['required', 'string', 'max:5000'],
                ],
                'requested_amount' => [
                    'label' => 'Requested Amount',
                    'type' => 'number',
                    'min' => 0,
                    'step' => '0.01',
                    'target' => 'assistance',
                    'cast' => 'decimal',
                    'rules' => ['nullable', 'numeric', 'min:0'],
                ],
                'claimant_name' => [
                    'label' => 'Claimant Name',
                    'type' => 'text',
                    'target' => 'assistance',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
                'relationship_to_beneficiary' => [
                    'label' => 'Relationship to Beneficiary',
                    'type' => 'text',
                    'target' => 'assistance',
                    'rules' => ['nullable', 'string', 'max:255'],
                ],
            ],
            'attachments' => [
                'certificate_of_enrollment' => [
                    'label' => 'Certificate of Enrollment',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'school_id' => [
                    'label' => 'School ID',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
                'assessment_or_billing' => [
                    'label' => 'Assessment or Billing Statement',
                    'required' => true,
                    'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
                ],
            ],
            'review_checklist' => [
                'Education case summary is clear and relevant.',
                'School enrollment and identity records are complete.',
                'Requested amount is supported by school assessment or billing.',
            ],
        ],
    ],

    'default_attachment_rules' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],

];
