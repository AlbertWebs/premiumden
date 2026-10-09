<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleBodyFormatter
{
    private const ALLOWED_TAGS = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li', 'blockquote', 'a'];

    public function render(string $body): string
    {
        if (! preg_match('/<\/?[a-z][^>]*>/i', $body)) {
            $paragraphs = preg_split('/\R{2,}/', trim($body)) ?: [];
            return implode('', array_map(fn (string $paragraph) => '<p>'.nl2br(e($paragraph)).'</p>', array_filter($paragraphs, 'strlen')));
        }

        return $this->sanitize($body);
    }

    public function sanitize(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><!doctype html><html><body><div id="article-body-root">'.$html.'</div></body></html>', LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('article-body-root');
        if (! $root) {
            return e(strip_tags($html));
        }

        $this->cleanChildren($root);
        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $dom->saveHTML($child);
        }

        return $clean;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math'], true)) {
                    $parent->removeChild($child);
                    continue;
                }
                $this->cleanChildren($child);
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            $href = $tag === 'a' ? $child->getAttribute('href') : '';
            while ($child->attributes->length) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
            if ($tag === 'a' && $this->safeHref($href)) {
                $child->setAttribute('href', $href);
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            $this->cleanChildren($child);
        }
    }

    private function safeHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, '//')) {
            return false;
        }
        $scheme = parse_url($href, PHP_URL_SCHEME);
        return $scheme === null || in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);
    }
}
