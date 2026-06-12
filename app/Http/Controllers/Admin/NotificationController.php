<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Notifications\ArchiveNotificationAction;
use App\Actions\Notifications\MarkAllNotificationsReadAction;
use App\Actions\Notifications\MarkNotificationReadAction;
use App\Actions\Notifications\SnoozeNotificationAction;
use App\Actions\Notifications\UpdateNotificationRulesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\ArchiveNotificationRequest;
use App\Http\Requests\Notifications\MarkAllNotificationsReadRequest;
use App\Http\Requests\Notifications\MarkNotificationRequest;
use App\Http\Requests\Notifications\NotificationFilterRequest;
use App\Http\Requests\Notifications\SnoozeNotificationRequest;
use App\Http\Requests\Notifications\UpdateNotificationRulesRequest;
use App\Models\SystemNotification;
use App\Services\Notifications\NotificationDependencyChecker;
use App\Services\Notifications\NotificationDigestService;
use App\Services\Notifications\NotificationRuleStore;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(
        NotificationFilterRequest $request,
        NotificationService $notifications,
        NotificationRuleStore $rules,
        NotificationDependencyChecker $dependencies,
        NotificationDigestService $digest,
    ): View {
        $user = $request->user();
        $notificationRules = $rules->all();

        return view('dashboard.notifications.index', [
            'adminUser' => $user,
            'filters' => $request->filters(),
            'notifications' => $notifications->feed($user, $request->filters()),
            'summary' => $notifications->summary($user),
            'selectedNotification' => null,
            'selectedActionLink' => null,
            'notificationRules' => $notificationRules,
            'ruleLabels' => $rules->labels(),
            'ruleModules' => $rules->modules(),
            'roleOptions' => $rules->roleOptions(),
            'dependencyWarnings' => $dependencies->warnings($notificationRules),
            'digestReadiness' => $digest->readiness(),
            'canUpdateRules' => $user->can('update notification rules'),
        ]);
    }

    public function show(
        NotificationFilterRequest $request,
        SystemNotification $systemNotification,
        NotificationService $notifications,
        NotificationRuleStore $rules,
        NotificationDependencyChecker $dependencies,
        NotificationDigestService $digest,
    ): View {
        Gate::authorize('view notification', $systemNotification);

        $user = $request->user();
        $notificationRules = $rules->all();

        return view('dashboard.notifications.index', [
            'adminUser' => $user,
            'filters' => $request->filters(),
            'notifications' => $notifications->feed($user, $request->filters()),
            'summary' => $notifications->summary($user),
            'selectedNotification' => $systemNotification,
            'selectedActionLink' => $notifications->actionLink($systemNotification, $user),
            'notificationRules' => $notificationRules,
            'ruleLabels' => $rules->labels(),
            'ruleModules' => $rules->modules(),
            'roleOptions' => $rules->roleOptions(),
            'dependencyWarnings' => $dependencies->warnings($notificationRules),
            'digestReadiness' => $digest->readiness(),
            'canUpdateRules' => $user->can('update notification rules'),
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

    public function updateRules(UpdateNotificationRulesRequest $request, UpdateNotificationRulesAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->ruleSettings());

        return back()->with('status', 'Notification rules saved.');
    }

    public function snooze(
        SnoozeNotificationRequest $request,
        SystemNotification $systemNotification,
        SnoozeNotificationAction $action,
    ): RedirectResponse {
        $action->handle($request->user(), $systemNotification, (string) $request->validated('duration'));

        return back()->with('status', 'Notification snoozed.');
    }
}
