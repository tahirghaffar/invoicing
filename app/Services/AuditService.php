<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditService
{
    public function log(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?int $businessId = null
    ): AuditLog {

        /*
        |--------------------------------------------------------------------------
        | Resolve Business
        |--------------------------------------------------------------------------
        */

        $businessId = $businessId
            ?? session('current_business_id');


        /*
        |--------------------------------------------------------------------------
        | Request ID
        |--------------------------------------------------------------------------
        |
        | We will improve this later with middleware so all audit events inside
        | one HTTP request share the same UUID.
        |
        */

        $requestId = request()->attributes->get(
            'audit_request_id'
        );

        if (!$requestId) {

            $requestId = (string) Str::uuid();

            request()->attributes->set(
                'audit_request_id',
                $requestId
            );
        }


        return AuditLog::create([

            'business_id' => $businessId,

            'user_id' => auth()->id(),

            'action' => $action,

            'auditable_type' =>
                $auditable
                    ? get_class($auditable)
                    : null,

            'auditable_id' =>
                $auditable?->getKey(),

            'old_values' =>
                !empty($oldValues)
                    ? $oldValues
                    : null,

            'new_values' =>
                !empty($newValues)
                    ? $newValues
                    : null,

            'metadata' =>
                !empty($metadata)
                    ? $metadata
                    : null,

            'ip_address' =>
                request()->ip(),

            'user_agent' =>
                request()->userAgent(),

            'request_id' =>
                $requestId,
        ]);
    }
}
