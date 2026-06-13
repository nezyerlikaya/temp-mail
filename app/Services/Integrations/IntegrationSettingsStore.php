<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;
use App\Models\User;
use Illuminate\Support\Collection;

class IntegrationSettingsStore
{
    public function __construct(
        private readonly IntegrationRegistry $registry,
        private readonly IntegrationFieldRegistry $fields,
        private readonly IntegrationSecretStore $secrets,
    ) {}

    /** @return Collection<int, array<string, mixed>> */
    public function cards(string $category = 'all', string $environment = 'sandbox'): Collection
    {
        return $this->registry->integrations()
            ->when($category !== 'all', fn (Collection $items): Collection => $items->where('category', $category)->values())
            ->map(fn (array $definition): array => $this->present($definition['key'], $environment));
    }

    /** @return array<string, mixed> */
    public function present(string $key, string $environment): array
    {
        $definition = $this->registry->find($key);
        abort_unless($definition !== null, 404);

        $setting = $this->setting($key, $environment);
        $fields = $this->fields->fields($key);
        $payload = $setting?->payload ?? [];
        $maskedSecrets = $this->secrets->masked($setting?->encrypted_secrets, $this->fields->secretKeys($key));
        $checklist = $this->checklist($key, $payload, $maskedSecrets);
        $complete = collect($checklist)->every(fn (array $item): bool => $item['complete']);

        return [
            ...$definition,
            'environment' => $environment,
            'setting' => $setting,
            'fields' => $fields,
            'payload' => $payload,
            'masked_secrets' => $maskedSecrets,
            'is_active' => $setting?->is_active ?? false,
            'connection_status' => $setting?->connection_status ?? 'not_configured',
            'last_tested_at' => $setting?->last_tested_at,
            'configuration_complete' => $complete,
            'checklist' => $checklist,
        ];
    }

    /** @param array<string, mixed> $data */
    public function update(string $key, string $environment, array $data, User $actor): IntegrationSetting
    {
        abort_unless($this->registry->find($key), 404);

        $existing = $this->setting($key, $environment);
        $payload = collect($this->fields->fields($key))
            ->reject(fn (array $field): bool => $field['type'] === 'secret')
            ->mapWithKeys(function (array $field) use ($data): array {
                $value = $data['settings'][$field['key']] ?? null;

                return [$field['key'] => $field['type'] === 'boolean' ? (bool) $value : trim((string) $value)];
            })
            ->filter(function (mixed $value): bool {
                return is_bool($value) || filled($value);
            })
            ->all();

        $existingSecrets = $this->secrets->decrypt($existing?->encrypted_secrets);
        $submittedSecrets = collect($this->fields->secretKeys($key))->mapWithKeys(fn (string $fieldKey): array => [
            $fieldKey => trim((string) ($data['secrets'][$fieldKey] ?? '')),
        ])->filter(fn (string $value): bool => $value !== '')->all();
        $mergedSecrets = [...$existingSecrets, ...$submittedSecrets];
        $masked = $this->secrets->masked($this->secrets->encrypt($mergedSecrets), $this->fields->secretKeys($key));
        $complete = collect($this->checklist($key, $payload, $masked))->every(fn (array $item): bool => $item['complete']);

        return IntegrationSetting::query()->updateOrCreate(
            ['integration_key' => $key, 'environment' => $environment],
            [
                'payload' => $payload,
                'encrypted_secrets' => $mergedSecrets === [] ? $existing?->encrypted_secrets : $this->secrets->encrypt($mergedSecrets),
                'is_active' => $existing?->is_active ?? false,
                'connection_status' => $complete ? ($existing?->connection_status ?? 'ready') : 'missing_configuration',
                'test_history' => $existing?->test_history ?? [],
                'last_tested_at' => $existing?->last_tested_at,
                'updated_by' => $actor->id,
            ],
        )->refresh();
    }

    public function activate(string $key, string $environment, User $actor): IntegrationSetting
    {
        abort_unless($this->registry->find($key), 404);

        $setting = $this->setting($key, $environment) ?? IntegrationSetting::query()->create([
            'integration_key' => $key,
            'environment' => $environment,
            'payload' => [],
            'encrypted_secrets' => null,
            'connection_status' => 'missing_configuration',
        ]);

        $setting->forceFill([
            'is_active' => true,
            'updated_by' => $actor->id,
        ])->save();

        return $setting->refresh();
    }

    public function deactivate(string $key, string $environment, User $actor): IntegrationSetting
    {
        abort_unless($this->registry->find($key), 404);

        $setting = $this->setting($key, $environment) ?? IntegrationSetting::query()->create([
            'integration_key' => $key,
            'environment' => $environment,
            'payload' => [],
            'encrypted_secrets' => null,
            'connection_status' => 'not_configured',
        ]);

        $setting->forceFill([
            'is_active' => false,
            'updated_by' => $actor->id,
        ])->save();

        return $setting->refresh();
    }

    public function secret(string $key, string $environment, string $field): ?string
    {
        if (! in_array($field, $this->fields->secretKeys($key), true)) {
            return null;
        }

        return $this->secrets->decrypt($this->setting($key, $environment)?->encrypted_secrets)[$field] ?? null;
    }

    private function setting(string $key, string $environment): ?IntegrationSetting
    {
        return IntegrationSetting::query()
            ->where('integration_key', $key)
            ->where('environment', $environment)
            ->first();
    }

    /** @return array<int, array{key: string, label: string, complete: bool, secret: bool}> */
    private function checklist(string $key, array $payload, array $maskedSecrets): array
    {
        return collect($this->fields->fields($key))
            ->filter(fn (array $field): bool => (bool) ($field['required'] ?? false))
            ->map(fn (array $field): array => [
                'key' => $field['key'],
                'label' => $field['label'],
                'secret' => $field['type'] === 'secret',
                'complete' => $field['type'] === 'secret'
                    ? filled($maskedSecrets[$field['key']] ?? null)
                    : filled($payload[$field['key']] ?? null),
            ])
            ->values()
            ->all();
    }
}
