<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'released_to_name',
        'released_to_relationship',
        'released_at',
        'released_by_user_id',
        'claimant_identification_notes',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_user_id');
    }
}
