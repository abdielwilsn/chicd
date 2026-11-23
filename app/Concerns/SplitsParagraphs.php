<?php

namespace App\Concerns;

/**
 * Turns plain text typed in the admin area into paragraphs, splitting on
 * blank lines, so the website can show it in its own typography.
 */
trait SplitsParagraphs
{
    /**
     * @return list<string>
     */
    protected function paragraphs(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), preg_split('/\R\s*\R/', trim($text)) ?: []),
            fn (string $paragraph): bool => $paragraph !== '',
        ));
    }
}
