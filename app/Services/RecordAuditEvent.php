<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RecordAuditEvent
{
    public function handle(string $action, Model $resource, array $metadata = [], ?int $actorId = null): void
    {
        AuditEvent::create([
            'actor_id' => $actorId ?? Auth::id(), 'action' => $action,
            'resource_type' => $resource::class, 'resource_id' => (string) $resource->getKey(),
            'metadata' => $metadata, 'created_at' => now(),
        ]);
    }
}
