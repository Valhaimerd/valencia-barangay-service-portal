<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'document_number',
        'file_path',
        'generated_at',
        'prepared_by_user_id',
        'printed_at',
        'printed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
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

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by_user_id');
    }
}
