<?php

declare(strict_types=1);

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitizer for rich-text fields.
 *
 * Anything not on the tag/attribute allow-list is unwrapped or dropped, so
 * script, style, event handlers, and javascript: URLs never survive a save.
 *
 * ponytail: hand-rolled instead of pulling in a purifier package. Ceiling —
 * it covers the small tag set this editor produces; widen ALLOWED_TAGS (or
 * move to a dedicated library) before accepting embeds or tables.
 */
class HtmlSanitizer
{
    /** @var array<string, list<string>> */
    private const ALLOWED_TAGS = [
        'p' => [],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'a' => ['href', 'title'],
        'figure' => [],
        'figcaption' => [],
        'img' => ['src', 'alt'],
        'div' => [],
        'span' => [],
    ];

    /** @var list<string> */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto'];

    public function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="sanitizer-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sanitizer-root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->cleanNode($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function cleanNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                    $this->unwrap($child);

                    continue;
                }

                $this->cleanAttributes($child, self::ALLOWED_TAGS[$tag]);
                $this->cleanNode($child);

                continue;
            }

            // Keep text, drop comments and processing instructions.
            if ($child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    /**
     * @param  list<string>  $allowed
     */
    private function cleanAttributes(DOMElement $element, array $allowed): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! $this->isSafeUrl($attribute->nodeValue)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if (strtolower($element->tagName) === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'noopener nofollow');
        }
    }

    private function isSafeUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, self::ALLOWED_SCHEMES, true);
    }

    /** Replace a disallowed element with its children, keeping the text. */
    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        $tag = strtolower($element->tagName);

        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
            $parent->removeChild($element);

            return;
        }

        $this->cleanNode($element);

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
