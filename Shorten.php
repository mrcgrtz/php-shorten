<?php

declare(strict_types=1);

namespace Marcgoertz\Shorten;

final class Shorten
{
    /** @var string Regex pattern for matching HTML entities */
    private const ENTITIES_PATTERN = '/&#?[a-zA-Z0-9]+;/i';

    /** @var string Regex pattern for matching HTML tags and entities */
    private const TAGS_AND_ENTITIES_PATTERN = '/<\/?([a-z0-9]+)[^>]*>|&#?[a-zA-Z0-9]+;/i';

    /** @var string[] Self-closing HTML tags */
    private const SELF_CLOSING_TAGS = [
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
        'command',
        'keygen',
        'menuitem',
    ];

    /**
     * Safely truncate text containing markup.
     *
     * @param string $markup         Text containing markup
     * @param int    $length         Maximum length of truncated text
     * @param string $appendix       Text added after truncated text
     * @param bool   $appendixInside Add appendix to last content in tags,
     *                               increases $length by 1
     * @param bool   $wordsafe       Wordsafe truncation
     * @param string $delimiter      Delimiter for wordsafe truncation
     *
     * @return string Truncated markup
     */
    public function truncateMarkup(
        string $markup,
        int $length = 400,
        string $appendix = '…',
        bool $appendixInside = false,
        bool $wordsafe = false,
        string $delimiter = ' '
    ): string {
        $this->validateParameters($length, $wordsafe, $delimiter);

        if ($this->shouldReturnEarly($markup, $length)) {
            return $this->handleEarlyReturn($markup, $length, $appendix, $appendixInside);
        }

        $hasEntities = (bool) preg_match(self::ENTITIES_PATTERN, $markup);

        if ($this->isMarkupShortEnough($markup, $length)) {
            return $markup;
        }

        $normalizedMarkup = $this->normalizeMarkup($markup);
        $truncationResult = $this->performTruncation($normalizedMarkup, $length);

        if ($wordsafe) {
            $truncationResult = $this->applyWordsafeTruncation(
                $truncationResult,
                $delimiter
            );
        }

        return $this->finalizeTruncation(
            $truncationResult,
            $appendix,
            $appendixInside,
            $hasEntities
        );
    }

    /**
     * Get the length of a string, using grapheme_strlen if available for proper emoji support.
     *
     * @param string $string The string to measure
     *
     * @return int The length of the string in grapheme units
     */
    private function getStringLength(string $string): int
    {
        return extension_loaded('intl') ? grapheme_strlen($string) : mb_strlen($string);
    }

    /**
     * Get a substring, using grapheme_substr if available for proper emoji support.
     *
     * @param string   $string The input string
     * @param int      $start  The start position
     * @param null|int $length The maximum length (null for no limit)
     *
     * @return string The substring
     */
    private function getSubstring(string $string, int $start, ?int $length = null): string
    {
        if (extension_loaded('intl')) {
            $result = null !== $length ? grapheme_substr($string, $start, $length) : grapheme_substr($string, $start);

            return false !== $result ? $result : '';
        }

        $result = null !== $length ? mb_substr($string, $start, $length) : mb_substr($string, $start);

        return false !== $result ? $result : '';
    }

    /**
     * Validate input parameters.
     *
     * @param int    &$length   Reference to length parameter (will be modified if negative)
     * @param bool   $wordsafe  Whether wordsafe truncation is enabled
     * @param string $delimiter Delimiter for wordsafe truncation
     *
     * @throws \InvalidArgumentException When delimiter is empty for wordsafe truncation
     */
    private function validateParameters(int &$length, bool $wordsafe, string $delimiter): void
    {
        if ($length < 0) {
            $length = 0;
        }

        if ($wordsafe && '' === $delimiter) {
            throw new \InvalidArgumentException('Delimiter cannot be empty for wordsafe truncation');
        }
    }

    /**
     * Check if we should return early without processing.
     *
     * @param string $markup The input markup
     * @param int    $length The target length
     *
     * @return bool True if should return early
     */
    private function shouldReturnEarly(string $markup, int $length): bool
    {
        return '' === trim($markup) || 0 === $length;
    }

    /**
     * Handle early return cases.
     *
     * @param string $markup         The input markup
     * @param int    $length         The target length
     * @param string $appendix       The appendix to add
     * @param bool   $appendixInside Whether to place appendix inside tags
     *
     * @return string The result for early return
     */
    private function handleEarlyReturn(
        string $markup,
        int $length,
        string $appendix,
        bool $appendixInside
    ): string {
        if ('' === trim($markup)) {
            return $markup;
        }

        return $appendixInside ? '' : $appendix;
    }

    /**
     * Check if markup is short enough and does not need truncation.
     *
     * @param string $markup The markup to check
     * @param int    $length The maximum allowed length
     *
     * @return bool True if markup is short enough
     */
    private function isMarkupShortEnough(string $markup, int $length): bool
    {
        $plainTextLength = $this->getStringLength(trim(strip_tags($markup)));

        return $plainTextLength <= $length;
    }

    /**
     * Normalize markup for processing.
     *
     * @param string $markup The original markup
     *
     * @return string The normalized markup with preserved emoji sequences
     */
    private function normalizeMarkup(string $markup): string
    {
        // Temporarily replace ZWJ sequences to prevent htmlentities from converting them
        $zwjPlaceholders = [];
        $zwjCounter = 0;

        // Find all ZWJ sequences and replace them temporarily
        $markup = preg_replace_callback('/[\x{200D}]/u', function ($match) use (&$zwjPlaceholders, &$zwjCounter) {
            $placeholder = "___ZWJ_{$zwjCounter}___";
            $zwjPlaceholders[$placeholder] = $match[0];
            ++$zwjCounter;

            return $placeholder;
        }, $markup);

        // Apply the original normalization
        $normalized = str_replace(
            ['&lt;', '&gt;', '&amp;'],
            ['<', '>', '&'],
            htmlentities($markup, ENT_NOQUOTES, 'UTF-8')
        );

        // Restore ZWJ characters
        return str_replace(array_keys($zwjPlaceholders), array_values($zwjPlaceholders), $normalized);
    }

    /**
     * Perform the main truncation logic.
     *
     * @param string $markup The normalized markup to truncate
     * @param int    $length The maximum length
     *
     * @return array{text: string, tags: string[]} Array containing truncated text and open tags
     */
    private function performTruncation(string $markup, int $length): array
    {
        $truncated = '';
        $lengthOutput = 0;
        $position = 0;
        $tags = [];
        $markupLength = mb_strlen($markup); // Keep mb_strlen for byte position

        // loop through text
        while (
            $lengthOutput < $length
            && preg_match(
                self::TAGS_AND_ENTITIES_PATTERN,
                $markup,
                $match,
                PREG_OFFSET_CAPTURE,
                $position
            )
        ) {
            [$tag, $positionTag] = $match[0];

            // add text leading up to the tag or entity
            $text = substr($markup, $position, $positionTag - $position);
            if ($lengthOutput + $this->getStringLength($text) > $length) {
                $truncated .= $this->getSubstring($text, 0, $length - $lengthOutput);
                $lengthOutput = $length;

                break;
            }
            $truncated .= $text;
            $lengthOutput += $this->getStringLength($text);

            $result = $this->processTagOrEntity($tag, $match, $tags, $positionTag);
            if ($result['skip']) {
                $position = $result['newPosition'];

                continue;
            }

            $truncated .= $tag;
            if ($result['incrementLength']) {
                ++$lengthOutput;
            }

            // continue after the tag
            $position = $positionTag + mb_strlen($tag); // Keep mb_strlen for byte position
        }

        // add any remaining text
        if ($lengthOutput < $length && $position < $markupLength) {
            $remainingText = substr($markup, $position);
            $truncated .= $this->getSubstring($remainingText, 0, $length - $lengthOutput);
        }

        return ['text' => $truncated, 'tags' => $tags];
    }

    /**
     * Process a single tag or entity.
     *
     * @param string                               $tag         The tag or entity to process
     * @param array<int, array{0: string, 1: int}> $match       Regex match result from preg_match
     * @param string[]                             &$tags       Reference to the stack of open tags
     * @param int                                  $positionTag Position of the tag in the markup
     *
     * @return array{skip: bool, incrementLength?: bool, newPosition?: int} Processing result
     */
    private function processTagOrEntity(string $tag, array $match, array &$tags, int $positionTag): array
    {
        if ('&' === $tag[0]) {
            // handle the entity (counts as one character)
            return ['skip' => false, 'incrementLength' => true];
        }

        // handle the tag
        $tagName = $match[1][0];

        if ('/' === $tag[1]) {
            return $this->handleClosingTag($tagName, $tags, $tag, $positionTag);
        }

        if ($this->isSelfClosingTag($tag, $tagName)) {
            return ['skip' => false, 'incrementLength' => false];
        }

        // opening tag
        $tags[] = $tagName;

        return ['skip' => false, 'incrementLength' => false];
    }

    /**
     * Handle closing tag processing.
     *
     * @param string   $tagName     The name of the closing tag
     * @param string[] &$tags       Reference to the stack of open tags
     * @param string   $tag         The complete tag string
     * @param int      $positionTag Position of the tag in the markup
     *
     * @return array{skip: bool, incrementLength?: bool, newPosition?: int} Processing result
     */
    private function handleClosingTag(string $tagName, array &$tags, string $tag, int $positionTag): array
    {
        $openingTag = array_pop($tags);

        // check that tags are properly nested
        if ($openingTag !== $tagName) {
            // Malformed HTML - attempt to recover by ignoring the mismatched closing tag
            if (null !== $openingTag) {
                $tags[] = $openingTag;
            }

            // Skip this malformed closing tag
            return ['skip' => true, 'newPosition' => $positionTag + mb_strlen($tag)];
        }

        return ['skip' => false, 'incrementLength' => false];
    }

    /**
     * Check if a tag is self-closing.
     *
     * @param string $tag     The complete tag string
     * @param string $tagName The tag name
     *
     * @return bool True if the tag is self-closing
     */
    private function isSelfClosingTag(string $tag, string $tagName): bool
    {
        // self-closing tag in XML dialect
        if ('/' === $tag[mb_strlen($tag) - 2]) {
            return true;
        }

        // self-closing tag in non-XML dialect
        return in_array($tagName, self::SELF_CLOSING_TAGS);
    }

    /**
     * Apply wordsafe truncation logic.
     *
     * @param array{text: string, tags: string[]} $truncationResult Result from performTruncation
     * @param string                              $delimiter        Delimiter for word boundaries
     *
     * @return array{text: string, tags: string[]} Updated truncation result
     */
    private function applyWordsafeTruncation(array $truncationResult, string $delimiter): array
    {
        $truncated = $truncationResult['text'];
        $tags = $truncationResult['tags'];

        $delimiterPosition = mb_strrpos($truncated, $delimiter);
        if (false === $delimiterPosition) {
            return $truncationResult;
        }

        // cut at delimiter position (use mb_substr for byte-accurate cutting)
        $truncated = mb_substr($truncated, 0, $delimiterPosition);

        // ensure we do not have incomplete tags after wordsafe truncation
        if ($this->hasIncompleteTag($truncated)) {
            $truncated = $this->removeIncompleteTag($truncated);
        }

        // Always recalculate tags after wordsafe truncation since content changed
        $tags = $this->recalculateOpenTags($truncated);

        return ['text' => $truncated, 'tags' => $tags];
    }

    /**
     * Check if truncated text has incomplete tags.
     *
     * @param string $truncated The truncated text to check
     *
     * @return bool True if there are incomplete tags
     */
    private function hasIncompleteTag(string $truncated): bool
    {
        $lastOpenBracket = mb_strrpos($truncated, '<');
        $lastCloseBracket = mb_strrpos($truncated, '>');

        return false !== $lastOpenBracket
               && (false === $lastCloseBracket || $lastOpenBracket > $lastCloseBracket);
    }

    /**
     * Remove incomplete tag from truncated text.
     *
     * @param string $truncated The text with incomplete tag
     *
     * @return string The text with incomplete tag removed
     */
    private function removeIncompleteTag(string $truncated): string
    {
        $lastOpenBracket = mb_strrpos($truncated, '<');

        return rtrim(mb_substr($truncated, 0, $lastOpenBracket));
    }

    /**
     * Recalculate which tags are still open after wordsafe truncation.
     *
     * @param string $truncated The truncated text to analyze
     *
     * @return string[] Array of tag names that are still open
     */
    private function recalculateOpenTags(string $truncated): array
    {
        $tags = [];
        $tempPosition = 0;

        while (
            preg_match(
                self::TAGS_AND_ENTITIES_PATTERN,
                $truncated,
                $match,
                PREG_OFFSET_CAPTURE,
                $tempPosition
            )
        ) {
            [$tag, $positionTag] = $match[0];

            if ('&' !== $tag[0]) {
                $tagName = $match[1][0];
                if (mb_strlen($tag) > 1 && '/' === $tag[1]) {
                    // Closing tag - only pop if it matches the last opened tag
                    $openingTag = array_pop($tags);
                    if ($openingTag !== $tagName && null !== $openingTag) {
                        // Tags do not match - push the opening tag back
                        $tags[] = $openingTag;
                    }
                } elseif (!$this->isSelfClosingTag($tag, $tagName)) {
                    // Opening tag (not self-closing)
                    $tags[] = $tagName;
                }
            }

            $tempPosition = $positionTag + mb_strlen($tag);
        }

        return $tags;
    }

    /**
     * Finalize truncation by adding appendix and closing tags.
     *
     * @param array{text: string, tags: string[]} $truncationResult Result from truncation
     * @param string                              $appendix         The appendix text to add
     * @param bool                                $appendixInside   Whether to place appendix inside tags
     * @param bool                                $hasEntities      Whether the original markup contained HTML entities
     *
     * @return string The final truncated markup
     */
    private function finalizeTruncation(
        array $truncationResult,
        string $appendix,
        bool $appendixInside,
        bool $hasEntities
    ): string {
        $truncated = $truncationResult['text'];
        $tags = $truncationResult['tags'];

        // add appendix to last tag content
        if ($appendixInside) {
            $truncated .= $appendix;
        }

        // close any open tags
        while (!empty($tags)) {
            $truncated .= sprintf('</%s>', array_pop($tags));
        }

        // decode entities again if markup did not contain any entities
        if (!$hasEntities) {
            $truncated = html_entity_decode($truncated, ENT_COMPAT, 'UTF-8');
        }

        return $appendixInside ? $truncated : $truncated.$appendix;
    }
}
