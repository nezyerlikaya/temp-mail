<?php

namespace App\Services\Mailboxes;

use DOMDocument;
use DOMElement;
use DOMNode;

class MessageSanitizer
{
    /** @var array<int, string> */
    private const BLOCKED_TAGS = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'base', 'svg', 'math'];

    public function sanitize(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div id="mail-body">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach (self::BLOCKED_TAGS as $tag) {
            while ($document->getElementsByTagName($tag)->length > 0) {
                $document->getElementsByTagName($tag)->item(0)?->parentNode?->removeChild($document->getElementsByTagName($tag)->item(0));
            }
        }

        foreach (iterator_to_array($document->getElementsByTagName('*')) as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                $value = trim($attribute->value);

                if (str_starts_with($name, 'on') || in_array($name, ['srcset', 'style'], true)) {
                    $element->removeAttribute($attribute->name);

                    continue;
                }

                if (in_array($name, ['src', 'href', 'background', 'poster'], true) && ! $this->safeResource($name, $value)) {
                    $element->removeAttribute($attribute->name);
                }
            }
        }

        $root = $document->getElementById('mail-body');

        return $root ? $this->innerHtml($root) : null;
    }

    private function safeResource(string $attribute, string $value): bool
    {
        if ($attribute === 'href') {
            return preg_match('/^(mailto:|#)/i', $value) === 1;
        }

        return preg_match('/^data:image\/(png|gif|jpe?g|webp);base64,/i', $value) === 1;
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return trim($html);
    }
}
