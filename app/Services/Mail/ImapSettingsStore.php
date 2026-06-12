<?php

namespace App\Services\Mail;

use App\Models\InboundMailConnection;
use App\Models\User;
use Illuminate\Support\Collection;

class ImapSettingsStore
{
    /** @return Collection<int, InboundMailConnection> */
    public function all(): Collection
    {
        return InboundMailConnection::query()
            ->with('domain')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): InboundMailConnection
    {
        return InboundMailConnection::query()->create([
            ...$this->payload($data),
            'encrypted_password' => (string) $data['password'],
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ])->refresh();
    }

    /** @param array<string, mixed> $data */
    public function update(InboundMailConnection $connection, array $data, User $actor): InboundMailConnection
    {
        $payload = [
            ...$this->payload($data),
            'updated_by' => $actor->id,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['encrypted_password'] = (string) $data['password'];
        }

        $connection->update($payload);

        return $connection->refresh();
    }

    public function activate(InboundMailConnection $connection, User $actor): InboundMailConnection
    {
        $connection->forceFill([
            'is_active' => true,
            'status' => $connection->status === 'disabled' ? 'not_tested' : $connection->status,
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    public function deactivate(InboundMailConnection $connection, User $actor): InboundMailConnection
    {
        $connection->forceFill([
            'is_active' => false,
            'status' => 'disabled',
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    /** @param array<string, mixed> $result */
    public function recordTest(InboundMailConnection $connection, array $result, User $actor): InboundMailConnection
    {
        $entry = [
            'status' => $result['status'],
            'message' => $result['message'],
            'checks' => $result['checks'],
            'tested_at' => now()->toIso8601String(),
        ];

        $history = collect($connection->health_history ?? [])
            ->prepend($entry)
            ->take(10)
            ->values()
            ->all();

        $connection->forceFill([
            'status' => $result['status'],
            'last_test_result' => $entry,
            'health_history' => $history,
            'last_tested_at' => now(),
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    /** @return array<string, mixed> */
    private function payload(array $data): array
    {
        return [
            'domain_id' => (int) $data['domain_id'],
            'name' => trim((string) $data['name']),
            'host' => str((string) $data['host'])->lower()->trim()->toString(),
            'port' => (int) $data['port'],
            'encryption' => (string) $data['encryption'],
            'username' => trim((string) $data['username']),
            'mailbox' => trim((string) ($data['mailbox'] ?? 'INBOX')) ?: 'INBOX',
            'connection_timeout' => (int) ($data['connection_timeout'] ?? 15),
            'validate_certificate' => (bool) ($data['validate_certificate'] ?? true),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'status' => (bool) ($data['is_active'] ?? false) ? 'not_tested' : 'disabled',
        ];
    }
}
