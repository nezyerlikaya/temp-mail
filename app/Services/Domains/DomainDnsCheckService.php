<?php

namespace App\Services\Domains;

use App\Models\Domain;
use Throwable;

class DomainDnsCheckService
{
    public function __construct(private readonly DomainVerificationService $verification) {}

    /** @return array<string, array<string, mixed>> */
    public function check(Domain $domain): array
    {
        $expected = $this->expectedRecords($domain);

        if (! function_exists('dns_get_record')) {
            return collect($expected)->mapWithKeys(fn (array $record, string $key): array => [
                $key => $this->result($record, [], 'unavailable', 'DNS lookup is unavailable on this hosting environment.'),
            ])->all();
        }

        try {
            $mx = $this->records($domain->domain_name, DNS_MX);
            $txt = $this->records($domain->domain_name, DNS_TXT);
            $dkim = $this->records('default._domainkey.'.$domain->domain_name, DNS_TXT);
            $dmarc = $this->records('_dmarc.'.$domain->domain_name, DNS_TXT);
            $ownership = $this->records($expected['ownership']['host'], DNS_TXT);

            return [
                'mx' => $this->result($expected['mx'], $this->mxValues($mx), $mx !== [] ? 'ready' : 'missing', $mx !== [] ? 'MX routing detected.' : 'Add an MX record for inbound routing readiness.'),
                'spf' => $this->txtResult($expected['spf'], $txt, 'v=spf1', 'Add an SPF TXT policy for sender authorization readiness.'),
                'dkim' => $this->txtResult($expected['dkim'], $dkim, 'v=DKIM1', 'Publish a DKIM TXT record when outbound signing is configured.'),
                'dmarc' => $this->txtResult($expected['dmarc'], $dmarc, 'v=DMARC1', 'Add a DMARC TXT policy to declare handling guidance.'),
                'ownership' => $this->txtResult($expected['ownership'], $ownership, (string) $expected['ownership']['value'], 'Publish the verification TXT value to prove domain control.'),
                'catch_all' => $this->result($expected['catch_all'], [], $domain->catch_all_ready ? 'ready' : 'pending', $domain->catch_all_ready ? 'Catch-all readiness confirmed manually.' : 'Confirm catch-all routing after the inbound mail provider is configured.'),
            ];
        } catch (Throwable) {
            return collect($expected)->mapWithKeys(fn (array $record, string $key): array => [
                $key => $this->result($record, [], 'unavailable', 'DNS check could not complete on this hosting environment. Try again later.'),
            ])->all();
        }
    }

    /** @return array<string, array<string, string>> */
    public function expectedRecords(Domain $domain): array
    {
        return [
            'mx' => ['type' => 'MX', 'host' => $domain->domain_name, 'value' => 'Inbound mail provider target'],
            'spf' => ['type' => 'TXT', 'host' => $domain->domain_name, 'value' => 'v=spf1 include:your-mail-provider -all'],
            'dkim' => ['type' => 'TXT', 'host' => 'default._domainkey.'.$domain->domain_name, 'value' => 'v=DKIM1; k=rsa; p=provider-public-key'],
            'dmarc' => ['type' => 'TXT', 'host' => '_dmarc.'.$domain->domain_name, 'value' => 'v=DMARC1; p=none'],
            'ownership' => $this->verification->expectedOwnershipRecord($domain),
            'catch_all' => ['type' => 'Routing', 'host' => '*@'.$domain->domain_name, 'value' => 'Catch-all inbox route'],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function records(string $host, int $type): array
    {
        $records = @dns_get_record($host, $type);

        return is_array($records) ? $records : [];
    }

    /** @param array<int, array<string, mixed>> $records */
    private function mxValues(array $records): array
    {
        return collect($records)
            ->map(fn (array $record): string => trim(($record['pri'] ?? '10').' '.($record['target'] ?? '')))
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<int, array<string, mixed>> $records */
    private function txtResult(array $expected, array $records, string $needle, string $remediation): array
    {
        $values = collect($records)
            ->map(fn (array $record): string => (string) ($record['txt'] ?? implode('', $record['entries'] ?? [])))
            ->filter()
            ->values()
            ->all();
        $ready = collect($values)->contains(fn (string $value): bool => str_contains($value, $needle));

        return $this->result($expected, $values, $ready ? 'ready' : 'missing', $ready ? 'Expected DNS policy detected.' : $remediation);
    }

    /** @param array<int, string> $detected */
    private function result(array $expected, array $detected, string $status, string $guidance): array
    {
        return [
            'type' => $expected['type'],
            'host' => $expected['host'],
            'expected' => $expected['value'],
            'detected' => $detected,
            'status' => $status,
            'guidance' => $guidance,
        ];
    }
}
