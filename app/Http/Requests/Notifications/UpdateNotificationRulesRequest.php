<?php

namespace App\Http\Requests\Notifications;

use App\Services\Notifications\NotificationRuleStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotificationRulesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update notification rules') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $store = app(NotificationRuleStore::class);
        $events = array_keys($store->defaults());
        $roles = $store->roleOptions();

        return [
            'rules' => ['required', 'array'],
            'rules.*.event_key' => ['required', Rule::in($events)],
            'rules.*.in_app_enabled' => ['nullable', 'boolean'],
            'rules.*.email_enabled' => ['nullable', 'boolean'],
            'rules.*.recipient_roles' => ['nullable', 'array'],
            'rules.*.recipient_roles.*' => ['string', Rule::in($roles)],
            'rules.*.digest_mode' => ['required', Rule::in(['immediate', 'daily'])],
            'rules.*.quiet_hours_enabled' => ['nullable', 'boolean'],
            'rules.*.quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'rules.*.quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'rules.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function ruleSettings(): array
    {
        return collect($this->validated('rules', []))
            ->mapWithKeys(fn (array $rule): array => [
                $rule['event_key'] => [
                    'in_app_enabled' => (bool) ($rule['in_app_enabled'] ?? false),
                    'email_enabled' => (bool) ($rule['email_enabled'] ?? false),
                    'recipient_roles' => $rule['recipient_roles'] ?? [],
                    'digest_mode' => $rule['digest_mode'],
                    'quiet_hours_enabled' => (bool) ($rule['quiet_hours_enabled'] ?? false),
                    'quiet_hours_start' => $rule['quiet_hours_start'] ?? null,
                    'quiet_hours_end' => $rule['quiet_hours_end'] ?? null,
                    'is_active' => (bool) ($rule['is_active'] ?? false),
                ],
            ])->all();
    }
}
