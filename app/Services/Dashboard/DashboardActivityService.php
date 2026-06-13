<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\UserAuditEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardActivityService
{
    /** @return array<int, array<string, mixed>> */
    public function recent(User $user): array
    {
        try {
            if (! Schema::hasTable('user_audit_events')) {
                return [];
            }

            return UserAuditEvent::query()
                ->with('actor')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (UserAuditEvent $event): array => [
                    'title' => $event->action ?: str($event->event)->replace(['.', '_'], ' ')->headline()->toString(),
                    'event' => $event->event,
                    'module' => $event->module ?: 'system',
                    'severity' => $event->severity ?: 'info',
                    'actor' => $event->actor?->name ?: 'System',
                    'time' => $event->created_at?->diffForHumans() ?? 'Just now',
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }
}
