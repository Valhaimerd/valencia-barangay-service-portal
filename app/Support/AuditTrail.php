<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditTrail
{
    public static function record(
        ?User $user,
        string $action,
        Model|string|null $subject = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null,
    ): void {
        AuditLog::create([
            'user_id' => $user?->id,
            'auditable_type' => $subject instanceof Model ? $subject->getMorphClass() : (is_string($subject) ? $subject : null),
            'auditable_id' => $subject instanceof Model ? $subject->getKey() : null,
            'action' => $action,
            'description' => $description,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
