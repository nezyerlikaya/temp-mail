<?php

namespace App\Services\Installer;

use RuntimeException;

class EnvironmentManager
{
    public function ensureEnvironmentFile(): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            $example = base_path('.env.example');

            if (! file_exists($example)) {
                throw new RuntimeException('The .env.example file is missing. Upload it before running the installer.');
            }

            copy($example, $path);
        }

        if (! $this->value('APP_KEY')) {
            $this->write([
                'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            ]);
        }
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function write(array $values): void
    {
        $path = base_path('.env');
        $contents = file_exists($path) ? file_get_contents($path) : '';

        if ($contents === false) {
            throw new RuntimeException('Unable to read the .env file.');
        }

        foreach ($values as $key => $value) {
            $encoded = $this->encode($value ?? '');
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, "{$key}={$encoded}", $contents) ?? $contents;
            } else {
                $contents = rtrim($contents).PHP_EOL."{$key}={$encoded}".PHP_EOL;
            }
        }

        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write the .env file.');
        }
    }

    public function value(string $key): ?string
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            return null;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (str_starts_with($line, $key.'=')) {
                return trim(substr($line, strlen($key) + 1), "\"'");
            }
        }

        return null;
    }

    private function encode(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|=|"|\'/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
