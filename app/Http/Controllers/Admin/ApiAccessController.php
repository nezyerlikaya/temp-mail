<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Api\CreateApiKeyAction;
use App\Actions\Api\RegenerateApiKeyAction;
use App\Actions\Api\RevokeApiKeyAction;
use App\Actions\Api\UpdateApiSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateApiKeyRequest;
use App\Http\Requests\Api\RegenerateApiKeyRequest;
use App\Http\Requests\Api\RevokeApiKeyRequest;
use App\Http\Requests\Api\UpdateApiSettingsRequest;
use App\Models\ApiKey;
use App\Models\User;
use App\Services\Api\ApiAccessPolicyService;
use App\Services\Api\ApiDocumentationService;
use App\Services\Api\ApiRateLimitResolver;
use App\Services\Api\ApiScopeRegistry;
use App\Services\Api\ApiSettingsStore;
use App\Services\Api\ApiUsageTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiAccessController extends Controller
{
    public function index(
        Request $request,
        ApiSettingsStore $settings,
        ApiScopeRegistry $scopes,
        ApiAccessPolicyService $policy,
        ApiDocumentationService $docs,
        ApiUsageTracker $usage,
        ApiRateLimitResolver $limits,
    ): View {
        $user = $request->user();
        $canManage = $user?->can('manage API globally') ?? false;
        $keys = ApiKey::query()
            ->with('user')
            ->when(! $canManage, fn ($query) => $query->whereBelongsTo($user))
            ->latest()
            ->paginate(12);

        return view('dashboard.api-access.index', [
            'adminUser' => $user,
            'settings' => $settings->get(),
            'scopes' => $scopes->all(),
            'keys' => $keys,
            'users' => $canManage
                ? User::query()->where('status', 'active')->orderBy('email')->limit(100)->get(['id', 'name', 'email', 'current_plan_reference', 'membership_status'])
                : collect([$user]),
            'canManageGlobally' => $canManage,
            'canCreateOwnKey' => $user ? $policy->canCreateFor($user, $user) : false,
            'documentation' => $docs->payload(),
            'usageSummary' => $canManage
                ? $usage->platformSummary($keys->sum(fn (ApiKey $key): int => $limits->monthlyLimit($key)))
                : $usage->summary($user, 0),
            'requestLogs' => $canManage ? $usage->recentPlatform() : $usage->recent($user),
        ]);
    }

    public function store(CreateApiKeyRequest $request, CreateApiKeyAction $action): RedirectResponse
    {
        $result = $action->handle($request->user(), $request->owner(), $request->validated());

        return back()
            ->with('status', 'API key created.')
            ->with('api_secret_once', [
                'secret' => $result['secret'],
                'prefix' => $result['key']->key_prefix,
                'name' => $result['key']->name,
            ]);
    }

    public function revoke(RevokeApiKeyRequest $request, ApiKey $apiKey, RevokeApiKeyAction $action): RedirectResponse
    {
        $action->handle($request->user(), $apiKey);

        return back()->with('status', 'API key revoked.');
    }

    public function regenerate(RegenerateApiKeyRequest $request, ApiKey $apiKey, RegenerateApiKeyAction $action): RedirectResponse
    {
        $result = $action->handle($request->user(), $apiKey);

        return back()
            ->with('status', 'API key regenerated.')
            ->with('api_secret_once', [
                'secret' => $result['secret'],
                'prefix' => $result['key']->key_prefix,
                'name' => $result['key']->name,
            ]);
    }

    public function settings(UpdateApiSettingsRequest $request, UpdateApiSettingsAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return back()->with('status', 'API access settings updated.');
    }
}
