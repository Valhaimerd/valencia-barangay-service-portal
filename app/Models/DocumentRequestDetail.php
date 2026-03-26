<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'purpose',
        'cedula_number',
        'cedula_date',
        'cedula_place',
        'years_of_residency',
        'months_of_residency',
        'jobseeker_availment_count',
        'oath_required',
        'payment_amount',
        'official_receipt_number',
        'prepared_by_user_id',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'cedula_date' => 'date',
            'oath_required' => 'boolean',
            'payment_amount' => 'decimal:2',
            'printed_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }
}
