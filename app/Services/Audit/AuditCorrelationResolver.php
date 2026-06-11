<?php

namespace App\Services\Audit;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditCorrelationResolver
{
    public function resolve(?Request $request = null): string
    {
        $request ??= request();

        $fromHeader = (string) $request->headers->get('X-Request-Id', '');

        if ($fromHeader !== '' && Str::length($fromHeader) <= 100) {
            return $fromHeader;
        }

        if ($request->hasSession()) {
            return (string) $request->session()->remember('audit_correlation_id', fn (): string => (string) Str::uuid());
        }

        return (string) Str::uuid();
    }
}
