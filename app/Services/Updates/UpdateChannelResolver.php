<?php

namespace App\Services\Updates;

class UpdateChannelResolver
{
    /** @return array<string, array{label: string, description: string}> */
    public function options(): array
    {
        return [
            'stable' => [
                'label' => 'Stable',
                'description' => 'Production-ready releases after standard verification.',
            ],
            'beta' => [
                'label' => 'Beta',
                'description' => 'Early access builds for staging and preview environments.',
            ],
            'security' => [
                'label' => 'Security',
                'description' => 'Focused security patches with minimal product change.',
            ],
        ];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->options());
    }

    public function default(): string
    {
        return $this->normalize((string) config('updates.default_channel', 'stable'));
    }

    public function normalize(?string $channel): string
    {
        return in_array($channel, $this->keys(), true) ? $channel : 'stable';
    }
}
