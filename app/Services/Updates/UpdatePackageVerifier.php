<?php

namespace App\Services\Updates;

class UpdatePackageVerifier
{
    public function __construct(private readonly UpdatePathProtector $paths) {}

    /**
     * @return array{status: string, checksum: string, signature_checked: bool, entries: array<int, string>}
     */
    public function verify(string $path, string $expectedChecksum, ?string $signature = null): array
    {
        if (! is_file($path)) {
            throw new \RuntimeException('The update package file is missing.');
        }

        $actualChecksum = hash_file('sha256', $path);

        if (! hash_equals(strtolower($expectedChecksum), strtolower((string) $actualChecksum))) {
            throw new \RuntimeException('The update package checksum does not match the manifest.');
        }

        $signatureChecked = false;

        if ($signature !== null && $signature !== '') {
            $publicKey = (string) config('updates.signature_public_key', '');

            if ($publicKey === '') {
                throw new \RuntimeException('The update package includes a signature, but no signature public key is configured.');
            }

            $verified = openssl_verify((string) file_get_contents($path), base64_decode($signature, true) ?: '', $publicKey, OPENSSL_ALGO_SHA256);

            if ($verified !== 1) {
                throw new \RuntimeException('The update package signature could not be verified.');
            }

            $signatureChecked = true;
        }

        return [
            'status' => 'verified',
            'checksum' => (string) $actualChecksum,
            'signature_checked' => $signatureChecked,
            'entries' => $this->paths->validateArchive($path),
        ];
    }
}
