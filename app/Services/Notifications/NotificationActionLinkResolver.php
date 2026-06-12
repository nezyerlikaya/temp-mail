<?php

namespace App\Services\Notifications;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Admin\AdminNavigationRegistry;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Throwable;

class NotificationActionLinkResolver
{
    public function __construct(private readonly AdminNavigationRegistry $navigation) {}

    /** @return array{label: string, url: string}|null */
    public function resolve(SystemNotification $notification, User $user): ?array
    {
        if ($notification->action_route === null || ! Route::has($notification->action_route)) {
            return null;
        }

        $item = $this->navigation->findByRoute($notification->action_route);

        if ($item !== null && Gate::forUser($user)->denies($item['permission'])) {
            return null;
        }

        try {
            return [
                'label' => $this->labelFor($notification),
                'url' => route($notification->action_route, $notification->action_parameters ?? []),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    private function labelFor(SystemNotification $notification): string
    {
        return match ($notification->related_module) {
            'content' => 'Review content',
            'trust' => 'Open security area',
            'mail-infrastructure' => 'Check mail infrastructure',
            'system' => 'Open system area',
            'billing' => 'Review billing',
            default => 'Open related area',
        };
    }
}
