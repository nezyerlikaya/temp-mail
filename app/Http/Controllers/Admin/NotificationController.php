<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Notifications\ArchiveNotificationAction;
use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Actions\Notifications\MarkNotificationReadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\ArchiveNotificationRequest;
use App\Http\Requests\Notifications\MarkAllNotificationsReadRequest;
use App\Http\Requests\Notifications\MarkNotificationRequest;
use App\Http\Requests\Notifications\NotificationFilterRequest;
use App\Models\SystemNotification;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(NotificationFilterRequest $request, NotificationService $notifications): View
    {
        $user = $request->user();

        return view('dashboard.notifications.index', [
            'adminUser' => $user,
            'filters' => $request->filters(),
            'notifications' => $notifications->feed($user, $request->filters()),
            'summary' => $notifications->summary($user),
            'selectedNotification' => null,
            'selectedActionLink' => null,
        ]);
    }

    public function show(NotificationFilterRequest $request, SystemNotification $systemNotification, NotificationService $notifications): View
    {
        Gate::authorize('view notification', $systemNotification);

        $user = $request->user();

        return view('dashboard.notifications.index', [
            'adminUser' => $user,
            'filters' => $request->filters(),
            'notifications' => $notifications->feed($user, $request->filters()),
            'summary' => $notifications->summary($user),
            'selectedNotification' => $systemNotification,
            'selectedActionLink' => $notifications->actionLink($systemNotification, $user),
        ]);
    }

    public function markRead(
        MarkNotificationRequest $request,
        SystemNotification $systemNotification,
        MarkNotificationReadAction $action,
    ): RedirectResponse {
        $action->handle($request->user(), $systemNotification);

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(MarkAllNotificationsReadRequest $request, MarkAllNotificationsReadAction $action): RedirectResponse
    {
        $count = $action->handle($request->user());

        return back()->with('status', $count === 1 ? '1 notification marked as read.' : $count.' notifications marked as read.');
    }

    public function archive(
        ArchiveNotificationRequest $request,
        SystemNotification $systemNotification,
        ArchiveNotificationAction $action,
    ): RedirectResponse {
        $action->handle($request->user(), $systemNotification);

        return redirect()->route('admin.notifications.index')->with('status', 'Notification archived.');
    }
}
