<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use Illuminate\Support\Str;

class BlockedListMatcher
{
    public function __construct(
        private readonly BlockedValueNormalizer $normalizer,
        private readonly BlockedListCacheService $cache,
        private readonly BlockedEntryExpiryService $expiry,
    ) {}

    /** @return array{decision: string, message: string, matched: bool, entry: array<string, mixed>|null, checked_type: string} */
    public function match(string $type, string $value): array
    {
        $this->expiry->expire();
        $normalized = $this->normalizeSample($type, $value);

        foreach ($this->cache->activeRules() as $rule) {
            if ($rule['entry_type'] === $type && $this->ruleMatches($rule, $normalized)) {
                return $this->result('blocked', 'An active blocked-list rule would block this value.', $rule, $type);
            }
        }

        $ignored = $this->ignoredRule($type, $normalized);
        if ($ignored !== null) {
            return $ignored;
        }

        return $this->result('allowed', 'No active blocked-list rule matched this value.', null, $type);
    }

    /** @return array{decision: string, message: string, matched: bool, entry: array<string, mixed>|null, checked_type: string} */
    public function matchAny(array $checks): array
    {
        foreach ($checks as $type => $value) {
            if (! filled($value)) {
                continue;
            }

            $result = $this->match((string) $type, (string) $value);

            if ($result['decision'] === 'blocked') {
                return $result;
            }
        }

        foreach ($checks as $type => $value) {
            if (! filled($value)) {
                continue;
            }

            $result = $this->match((string) $type, (string) $value);

            if (in_array($result['decision'], ['expired_rule_ignored', 'inactive_rule_ignored'], true)) {
                return $result;
            }
        }

        return $this->result('allowed', 'No active blocked-list rule matched this value.', null, 'multiple');
    }

    private function normalizeSample(string $type, string $value): string
    {
        if ($type === 'blocked_phrase') {
            return Str::squish(str(strip_tags($value))->lower()->toString());
        }

        return $this->normalizer->normalize($type, $value);
    }

    /** @param array<string, mixed> $rule */
    private function ruleMatches(array $rule, string $normalized): bool
    {
        return match ($rule['entry_type']) {
            'recipient_email_pattern' => $this->wildcardMatches((string) $rule['normalized_value'], $normalized),
            'ip_address' => $this->ipMatches((string) $rule['normalized_value'], $normalized),
            'blocked_phrase' => str_contains($normalized, (string) $rule['normalized_value']),
            default => hash_equals((string) $rule['normalized_hash'], $this->normalizer->hash($normalized)),
        };
    }

    private function wildcardMatches(string $pattern, string $value): bool
    {
        $regex = '/^'.str_replace('\*', '.*', preg_quote($pattern, '/')).'$/i';

        return preg_match($regex, $value) === 1;
    }

    private function ipMatches(string $rule, string $value): bool
    {
        if (! str_contains($rule, '/')) {
            return hash_equals($rule, $value);
        }

        [$network, $prefix] = explode('/', $rule, 2);
        $networkBytes = inet_pton($network);
        $valueBytes = inet_pton($value);

        if ($networkBytes === false || $valueBytes === false || strlen($networkBytes) !== strlen($valueBytes)) {
            return false;
        }

        $bits = (int) $prefix;
        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if ($fullBytes > 0 && substr($networkBytes, 0, $fullBytes) !== substr($valueBytes, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

        return (ord($networkBytes[$fullBytes]) & $mask) === (ord($valueBytes[$fullBytes]) & $mask);
    }

    /** @return array{decision: string, message: string, matched: bool, entry: array<string, mixed>|null, checked_type: string}|null */
    private function ignoredRule(string $type, string $normalized): ?array
    {
        $entries = BlockedListEntry::query()
            ->where('entry_type', $type)
            ->whereIn('status', ['inactive', 'expired', 'active'])
            ->get();

        foreach ($entries as $entry) {
            if (! $this->ruleMatches($this->cache->serialize($entry), $normalized)) {
                continue;
            }

            if ($entry->status === 'inactive') {
                return $this->result('inactive_rule_ignored', 'A matching inactive rule exists and is ignored.', $this->cache->serialize($entry), $type);
            }

            if ($entry->status === 'expired' || ($entry->expires_at !== null && $entry->expires_at->lte(now()))) {
                return $this->result('expired_rule_ignored', 'A matching expired rule exists and is ignored.', $this->cache->serialize($entry), $type);
            }
        }

        return null;
    }

    /** @return array{decision: string, message: string, matched: bool, entry: array<string, mixed>|null, checked_type: string} */
    private function result(string $decision, string $message, ?array $entry, string $type): array
    {
        return [
            'decision' => $decision,
            'message' => $message,
            'matched' => $decision === 'blocked',
            'entry' => $entry,
            'checked_type' => $type,
        ];
    }
}
