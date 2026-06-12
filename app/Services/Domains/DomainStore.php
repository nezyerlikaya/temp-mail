<?php

namespace App\Services\Domains;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DomainStore
{
    /** @return Collection<int, Domain> */
    public function all(): Collection
    {
        return Domain::query()->orderBy('sort_order')->orderBy('domain_name')->get();
    }

    /** @return array{total: int, active: int, public: int, ready: int, degraded: int, default: string|null} */
    public function summary(): array
    {
        return [
            'total' => Domain::query()->count(),
            'active' => Domain::query()->where('is_active', true)->count(),
            'public' => Domain::query()->where('is_public', true)->count(),
            'ready' => Domain::query()->where('status', 'ready')->count(),
            'degraded' => Domain::query()->where('status', 'degraded')->count(),
            'default' => Domain::query()->where('is_default', true)->value('domain_name'),
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): Domain
    {
        $payload = $this->payload($data);
        $payload['created_by'] = $actor->id;
        $payload['updated_by'] = $actor->id;

        if ((bool) ($payload['is_default'] ?? false)) {
            $payload['is_active'] = true;
        }

        $domain = Domain::query()->create($payload);

        if ($domain->is_default) {
            $this->setDefault($domain, $actor);
        } elseif (Domain::query()->count() === 1) {
            $this->setDefault($domain->forceFill(['is_active' => true]), $actor);
        }

        return $domain->refresh();
    }

    /** @param array<string, mixed> $data */
    public function update(Domain $domain, array $data, User $actor): Domain
    {
        $payload = $this->payload($data);
        $payload['updated_by'] = $actor->id;

        if ($domain->is_default) {
            $payload['is_active'] = true;
            $payload['is_default'] = true;
        }

        $domain->update($payload);

        if ((bool) ($payload['is_default'] ?? false)) {
            $this->setDefault($domain, $actor);
        }

        return $domain->refresh();
    }

    public function activate(Domain $domain, User $actor): Domain
    {
        $domain->forceFill([
            'is_active' => true,
            'status' => $domain->status === 'offline' ? 'pending_dns' : $domain->status,
            'updated_by' => $actor->id,
        ])->save();

        return $domain->refresh();
    }

    public function deactivate(Domain $domain, User $actor): Domain
    {
        if ($domain->is_default) {
            $this->fail('domain', 'The default domain must remain active. Choose a replacement first.');
        }

        if (Domain::query()->where('is_active', true)->whereKeyNot($domain->id)->count() < 1) {
            $this->fail('domain', 'At least one active domain is required before deactivating this domain.');
        }

        $domain->forceFill([
            'is_active' => false,
            'is_public' => false,
            'status' => 'offline',
            'updated_by' => $actor->id,
        ])->save();

        return $domain->refresh();
    }

    public function setDefault(Domain $domain, User $actor): Domain
    {
        if (! $domain->is_active) {
            $this->fail('domain', 'The default domain must be active.');
        }

        Domain::query()->whereKeyNot($domain->id)->update(['is_default' => false]);

        $domain->forceFill([
            'is_default' => true,
            'is_active' => true,
            'updated_by' => $actor->id,
        ])->save();

        return $domain->refresh();
    }

    /** @param array<string, array<string, mixed>> $checks */
    public function saveDnsCheck(Domain $domain, array $checks, string $status, User $actor): Domain
    {
        $domain->forceFill([
            'dns_checks' => $checks,
            'status' => $status,
            'last_checked_at' => now(),
            'updated_by' => $actor->id,
        ])->save();

        return $domain->refresh();
    }

    /** @param array<string, mixed> $data */
    private function payload(array $data): array
    {
        return [
            'domain_name' => str((string) $data['domain_name'])->lower()->trim()->toString(),
            'display_name' => (string) $data['display_name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_public' => (bool) ($data['is_public'] ?? false),
            'catch_all_ready' => (bool) ($data['catch_all_ready'] ?? false),
            'is_default' => (bool) ($data['is_default'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 100),
            'status' => (string) ($data['status'] ?? 'draft'),
        ];
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
