<?php

namespace App\Services\Mail;

use App\Models\SmtpConnection;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class SmtpSettingsStore
{
    /** @return Collection<int, SmtpConnection> */
    public function all(): Collection
    {
        return SmtpConnection::query()->with('domain')->orderByDesc('is_default')->orderBy('name')->get();
    }

    public function defaultConnection(): ?SmtpConnection
    {
        return SmtpConnection::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function applyRuntimeConfig(): void
    {
        if (! $this->tableIsReady()) {
            return;
        }

        $connection = $this->defaultConnection();

        if (! $connection) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $connection->encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => $connection->host,
            'mail.mailers.smtp.port' => $connection->port,
            'mail.mailers.smtp.username' => $connection->username,
            'mail.mailers.smtp.password' => (string) $connection->encrypted_password,
            'mail.mailers.smtp.timeout' => $connection->connection_timeout,
            'mail.mailers.smtp.auto_tls' => $connection->encryption !== 'none',
            'mail.mailers.smtp.verify_peer' => $connection->validate_certificate,
            'mail.from.address' => $connection->from_email,
            'mail.from.name' => $connection->from_name,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): SmtpConnection
    {
        $payload = $this->payload($data);

        if ((bool) $payload['is_default']) {
            $payload['is_active'] = true;
        }

        $connection = SmtpConnection::query()->create([
            ...$payload,
            'encrypted_password' => (string) $data['password'],
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        if ($connection->is_default || SmtpConnection::query()->count() === 1) {
            $this->setDefault($connection->forceFill(['is_active' => true]), $actor);
        }

        return $connection->refresh();
    }

    /** @param array<string, mixed> $data */
    public function update(SmtpConnection $connection, array $data, User $actor): SmtpConnection
    {
        $payload = [
            ...$this->payload($data),
            'updated_by' => $actor->id,
        ];

        if ($connection->is_default) {
            $payload['is_default'] = true;
            $payload['is_active'] = true;
        }

        if (filled($data['password'] ?? null)) {
            $payload['encrypted_password'] = (string) $data['password'];
        }

        $connection->update($payload);

        if ((bool) $payload['is_default']) {
            $this->setDefault($connection, $actor);
        }

        return $connection->refresh();
    }

    public function activate(SmtpConnection $connection, User $actor): SmtpConnection
    {
        $connection->forceFill([
            'is_active' => true,
            'status' => $connection->status === 'disabled' ? 'not_tested' : $connection->status,
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    public function deactivate(SmtpConnection $connection, User $actor): SmtpConnection
    {
        if ($connection->is_default) {
            throw ValidationException::withMessages(['smtp' => 'The default SMTP connection must remain active. Choose a replacement first.']);
        }

        $connection->forceFill([
            'is_active' => false,
            'status' => 'disabled',
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    public function setDefault(SmtpConnection $connection, User $actor): SmtpConnection
    {
        if (! $connection->is_active) {
            throw ValidationException::withMessages(['smtp' => 'The default SMTP connection must be active.']);
        }

        SmtpConnection::query()->whereKeyNot($connection->id)->update(['is_default' => false]);

        $connection->forceFill([
            'is_default' => true,
            'is_active' => true,
            'updated_by' => $actor->id,
        ])->save();

        return $connection->refresh();
    }

    /** @param array<string, mixed> $result */
    public function recordTest(SmtpConnection $connection, array $result, User $actor): SmtpConnection
    {
        $entry = [
            'status' => $result['status'],
            'message' => $result['message'],
            'checks' => $result['checks'],
            'tested_at' => now()->toIso8601String(),
        ];

        $history = collect($connection->health_history ?? [])->prepend($entry)->take(10)->values()->all();

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
            'domain_id' => filled($data['domain_id'] ?? null) ? (int) $data['domain_id'] : null,
            'name' => trim((string) $data['name']),
            'host' => str((string) $data['host'])->lower()->trim()->toString(),
            'port' => (int) $data['port'],
            'encryption' => (string) $data['encryption'],
            'username' => trim((string) $data['username']),
            'from_email' => str((string) $data['from_email'])->lower()->trim()->toString(),
            'from_name' => trim((string) $data['from_name']),
            'reply_to_email' => filled($data['reply_to_email'] ?? null) ? str((string) $data['reply_to_email'])->lower()->trim()->toString() : null,
            'reply_to_ready' => filled($data['reply_to_email'] ?? null),
            'connection_timeout' => (int) ($data['connection_timeout'] ?? 15),
            'validate_certificate' => (bool) ($data['validate_certificate'] ?? true),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_default' => (bool) ($data['is_default'] ?? false),
            'status' => (bool) ($data['is_active'] ?? false) ? 'not_tested' : 'disabled',
        ];
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('smtp_connections');
        } catch (Throwable) {
            return false;
        }
    }
}
