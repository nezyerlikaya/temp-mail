<?php

namespace App\Services\Typography;

use App\Models\FontFamily;

class FontPairingService
{
    /** @param array<string, array<string, mixed>> $resolvedStacks */
    public function warnings(array $resolvedStacks): array
    {
        $heading = $resolvedStacks['heading']['font'] ?? null;
        $body = $resolvedStacks['body']['font'] ?? null;

        if (! $heading instanceof FontFamily || ! $body instanceof FontFamily) {
            return [['level' => 'warning', 'message' => 'Heading or body font is unresolved, so readability cannot be verified.']];
        }

        $warnings = [];

        if ($heading->slug !== $body->slug && $heading->category !== $body->category) {
            $warnings[] = ['level' => 'notice', 'message' => 'Heading and body use different categories. Check long-form readability before publishing.'];
        }

        if ($heading->category === 'mono' || $body->category === 'mono') {
            $warnings[] = ['level' => 'warning', 'message' => 'Monospace fonts in heading or body can harm readability for public content.'];
        }

        if ($heading->slug !== $body->slug && count($heading->supported_scripts ?? []) !== count($body->supported_scripts ?? [])) {
            $warnings[] = ['level' => 'notice', 'message' => 'Heading and body script coverage differs. Review multilingual pages before launch.'];
        }

        return $warnings;
    }
}
