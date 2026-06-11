<?php

namespace App\Actions\Settings;

use App\Actions\Media\AttachMediaUsageAction;
use App\Actions\Media\DetachMediaUsageAction;
use App\Models\MediaAsset;
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
        private readonly AttachMediaUsageAction $attachMediaUsage,
        private readonly DetachMediaUsageAction $detachMediaUsage,
    ) {}

    /** @param array<string, mixed> $settings */
    public function handle(User $actor, string $group, array $settings): void
    {
        DB::transaction(function () use ($actor, $group, $settings): void {
            $before = $this->resolver->group($group);
            $this->store->put($group, $settings, $actor);
            $this->syncMediaUsages($actor, $group, $before, $settings);
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

    /** @param array<string, mixed> $before @param array<string, mixed> $settings */
    private function syncMediaUsages(User $actor, string $group, array $before, array $settings): void
    {
        if ($group !== 'brand') {
            return;
        }

        foreach (['logo_media_id' => 'Logo', 'favicon_media_id' => 'Favicon', 'app_icon_media_id' => 'App icon'] as $field => $label) {
            if (($before[$field] ?? null) === ($settings[$field] ?? null)) {
                continue;
            }

            $usage = [
                'module' => 'settings',
                'usage_context' => 'brand',
                'slot' => $field,
                'usable_type' => 'system_settings',
                'usable_id' => 'brand',
            ];

            $this->detachMediaUsage->handle($actor, $usage);

            $asset = isset($settings[$field]) ? MediaAsset::query()->find((int) $settings[$field]) : null;
            if ($asset) {
                $this->attachMediaUsage->handle($actor, $asset, [
                    ...$usage,
                    'label' => $label,
                ]);
            }
        }
    }
}
