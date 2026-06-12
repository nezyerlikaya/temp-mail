<?php

namespace App\Services\Domains;

use App\Models\Domain;
use Illuminate\Support\Collection;

class DomainReadinessService
{
    /** @return array<string, mixed> */
    public function forDomain(Domain $domain): array
    {
        $checks = collect($domain->dns_checks ?? []);
        $ready = $checks->filter(fn (array $check): bool => ($check['status'] ?? null) === 'ready')->count();
        $total = max(1, $checks->count());
        $score = (int) round(($ready / $total) * 100);

        return [
            'score' => $checks->isEmpty() ? 0 : $score,
            'ready' => $ready,
            'total' => $checks->isEmpty() ? 6 : $total,
            'label' => $score >= 90 ? 'Ready' : ($score >= 40 ? 'Needs review' : 'Pending DNS'),
            'status' => $domain->status,
        ];
    }

    /** @param Collection<int, Domain> $domains */
    public function summary(Collection $domains): array
    {
        $checksReady = $domains->filter(fn (Domain $domain): bool => in_array($domain->status, ['ready', 'degraded'], true))->count();

        return [
            'dns_ready' => $checksReady,
            'needs_dns' => $domains->filter(fn (Domain $domain): bool => in_array($domain->status, ['draft', 'pending_dns'], true))->count(),
            'catch_all_ready' => $domains->where('catch_all_ready', true)->count(),
            'notification_ready' => $domains->whereIn('status', ['degraded', 'offline'])->count() > 0,
        ];
    }
}
