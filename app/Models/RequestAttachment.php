<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'attachment_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by_user_id',
        'is_required',
        'review_status',
        'reviewer_notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_required' => 'boolean',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
