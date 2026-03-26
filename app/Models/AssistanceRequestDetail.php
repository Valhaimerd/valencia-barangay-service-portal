<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistanceRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'case_summary',
        'requested_amount',
        'assessment_notes',
        'assessment_date',
        'claimant_name',
        'relationship_to_beneficiary',
        'referral_destination',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'assessment_date' => 'date',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }
}
