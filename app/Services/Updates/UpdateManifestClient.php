<?php

namespace App\Services\Updates;

use Illuminate\Support\Facades\Http;
use Throwable;

class UpdateManifestClient
{
    /**
     * @return array{ok: bool, endpoint: string, https: bool, manifest: array<string, mixed>|null, error: string|null}
     */
    public function fetch(string $channel): array
    {
        $endpoint = $this->endpoint();
        $isHttps = parse_url($endpoint, PHP_URL_SCHEME) === 'https';

        if (! $isHttps) {
            return $this->failed($endpoint, false, 'The update server must use HTTPS before update checks can run.');
        }

        try {
            $response = Http::acceptJson()
                ->timeout(8)
                ->retry(1, 250)
                ->get($endpoint, [
                    'channel' => $channel,
                    'version' => $this->currentVersion(),
                ]);
        } catch (Throwable) {
            return $this->failed($endpoint, true, 'The update server could not be reached. Please try again later.');
        }

        if (! $response->successful()) {
            return $this->failed($endpoint, true, 'The update server responded with an unavailable status.');
        }

        $manifest = $response->json();

        if (! is_array($manifest)) {
            return $this->failed($endpoint, true, 'The update server returned an unreadable manifest.');
        }

        return [
            'ok' => true,
            'endpoint' => $endpoint,
            'https' => true,
            'manifest' => $manifest,
            'error' => null,
        ];
    }

    public function endpoint(): string
    {
        return rtrim((string) config('updates.endpoint', 'https://www.doic.net/update'), '/');
    }

    public function currentVersion(): string
    {
        return (string) config('updates.current_version', '1.0.0');
    }

    /**
     * @return array{ok: false, endpoint: string, https: bool, manifest: null, error: string}
     */
    private function failed(string $endpoint, bool $https, string $error): array
    {
        return [
            'ok' => false,
            'endpoint' => $endpoint,
            'https' => $https,
            'manifest' => null,
            'error' => $error,
        ];
    }
}
