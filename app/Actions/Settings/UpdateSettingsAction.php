<?php

namespace App\Actions\Settings;

use App\Models\User;
use App\Services\Settings\SettingsResolver;
use App\Services\Settings\SettingsStore;
use App\Services\Users\UserAuditLogger;
use Illuminate\Support\Facades\DB;

class UpdateSettingsAction
{
    public function __construct(
        private readonly SettingsStore $store,
        private readonly SettingsResolver $resolver,
        private readonly UserAuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $settings */
    public function handle(User $actor, string $group, array $settings): void
    {
        DB::transaction(function () use ($actor, $group, $settings): void {
            $before = $this->resolver->group($group);
            $this->store->put($group, $settings, $actor);
            $this->resolver->applyRuntime();

            $changed = collect($settings)->filter(fn (mixed $value, string $key): bool => ($before[$key] ?? null) !== $value)->keys()->all();

            if ($changed !== []) {
                $this->audit->record($actor, $actor, 'system.settings_updated', [
                    'group' => $group,
                    'changed_keys' => $changed,
                ]);
            }
        });
    }

    public function reset(User $actor, string $group): void
    {
        DB::transaction(function () use ($actor, $group): void {
            $this->store->forget($group);
            $this->resolver->applyRuntime();
            $this->audit->record($actor, $actor, 'system.settings_reset', ['group' => $group]);
        });
    }
}
