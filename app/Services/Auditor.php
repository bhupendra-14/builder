<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Central writer for audit log entries. Use this from observers, services,
 * and controllers whenever an action that should appear on the Audit page
 * happens. Safe to call from console / seeders — falls back to user_id null.
 */
class Auditor
{
    public function log(string $action, ?string $targetType = null, ?int $targetId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        // Don't pollute the audit log when running migrations / seeders.
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 1000),
            ]);
        } catch (\Throwable $e) {
            // Audit log failures must never break the underlying operation.
            report($e);
        }
    }
}
