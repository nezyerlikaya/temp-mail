<?php

namespace App\Services\Seo;

use App\Models\SeoRedirect;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RedirectService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return Collection<int, SeoRedirect> */
    public function redirects(): Collection
    {
        return SeoRedirect::query()->latest()->limit(12)->get();
    }

    /** @param array<string, mixed> $payload */
    public function store(User $actor, array $payload): SeoRedirect
    {
        $payload = $this->normalize($payload);
        $this->guardAgainstConflicts($payload['source_path'], $payload['target_url']);

        $redirect = SeoRedirect::query()->create([
            ...$payload,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->audit->record('seo.redirect_created', $actor, null, [
            'seo_redirect_id' => $redirect->id,
            'source_path' => $redirect->source_path,
            'status_code' => $redirect->status_code,
        ], ['module' => 'seo', 'action' => 'Create redirect', 'target' => $redirect]);

        return $redirect;
    }

    /** @param array<string, mixed> $payload */
    public function update(User $actor, SeoRedirect $redirect, array $payload): SeoRedirect
    {
        $payload = $this->normalize($payload);
        $this->guardAgainstConflicts($payload['source_path'], $payload['target_url'], $redirect);

        $redirect->update([...$payload, 'updated_by' => $actor->id]);

        $this->audit->record('seo.redirect_updated', $actor, null, [
            'seo_redirect_id' => $redirect->id,
            'source_path' => $redirect->source_path,
            'status_code' => $redirect->status_code,
            'is_active' => $redirect->is_active,
        ], ['module' => 'seo', 'action' => 'Update redirect', 'target' => $redirect]);

        return $redirect->refresh();
    }

    /** @param array<string, mixed> $payload */
    private function normalize(array $payload): array
    {
        return [
            'source_path' => $this->path((string) $payload['source_path']),
            'target_url' => $this->target((string) $payload['target_url']),
            'status_code' => (int) $payload['status_code'],
            'is_active' => (bool) ($payload['is_active'] ?? false),
            'notes' => $payload['notes'] ?? null,
        ];
    }

    private function path(string $path): string
    {
        $path = trim($path);

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    private function target(string $target): string
    {
        $target = trim($target);

        if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
            return $target;
        }

        return str_starts_with($target, '/') ? $target : '/'.$target;
    }

    private function guardAgainstConflicts(string $sourcePath, string $targetUrl, ?SeoRedirect $current = null): void
    {
        $targetPath = parse_url($targetUrl, PHP_URL_PATH) ?: $targetUrl;

        if ($sourcePath === $targetPath) {
            throw ValidationException::withMessages([
                'target_url' => 'Redirect target cannot be the same as the source path.',
            ]);
        }

        $conflictingSource = SeoRedirect::query()
            ->where('source_path', $sourcePath)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        if ($conflictingSource) {
            throw ValidationException::withMessages([
                'source_path' => 'A redirect already exists for this source path.',
            ]);
        }

        $loop = SeoRedirect::query()
            ->where('source_path', $targetPath)
            ->where('target_url', $sourcePath)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        if ($loop) {
            throw ValidationException::withMessages([
                'target_url' => 'This redirect would create a loop with an existing redirect.',
            ]);
        }
    }
}
