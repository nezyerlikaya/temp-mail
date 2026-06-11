<?php

namespace App\Services\Updates;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UpdatePackageDownloader
{
    /**
     * @param  array<string, mixed>  $manifest
     * @return array{path: string, url: string, bytes: int}
     */
    public function download(array $manifest, string $endpoint, string $version): array
    {
        $url = (string) ($manifest['package_url'] ?? '');

        $this->assertTrustedUrl($url, $endpoint);

        $response = Http::timeout(30)->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('The update package could not be downloaded.');
        }

        $body = $response->body();
        $maxBytes = (int) config('updates.max_package_bytes', 64 * 1024 * 1024);

        if (strlen($body) > $maxBytes) {
            throw new \RuntimeException('The update package is larger than the configured safety limit.');
        }

        $directory = storage_path('app/update-center/packages');
        File::ensureDirectoryExists($directory);

        $path = $directory.DIRECTORY_SEPARATOR.'update-'.$version.'-'.now()->format('YmdHis').'.zip';
        File::put($path, $body, true);

        return [
            'path' => $path,
            'url' => $url,
            'bytes' => strlen($body),
        ];
    }

    public function assertTrustedUrl(string $url, string $endpoint): void
    {
        if ($url === '' || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new \RuntimeException('Update packages must be downloaded from an HTTPS URL in the verified manifest.');
        }

        $packageHost = parse_url($url, PHP_URL_HOST);
        $endpointHost = parse_url($endpoint, PHP_URL_HOST);

        if ($packageHost === null || $endpointHost === null || $packageHost !== $endpointHost) {
            throw new \RuntimeException('The update package host does not match the configured update server.');
        }
    }
}
