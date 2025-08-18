<?php

declare(strict_types=1);

namespace Marcgoertz\Shorten;

final class Shorten
{
    private const ENTITIES_PATTERN = '/&#?[a-zA-Z0-9]+;/i';
    private const TAGS_AND_ENTITIES_PATTERN = '/<\/?([a-z0-9]+)[^>]*>|&#?[a-zA-Z0-9]+;/i';
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
     * @param string $markup         text containing markup
     * @param int    $length         maximum length of truncated text
     * @param string $appendix       text added after truncated text
     * @param bool   $appendixInside add appendix to last content in tags,
     *                               increases $length by 1
     * @param bool   $wordsafe       wordsafe truncation
     * @param string $delimiter      delimiter for wordsafe truncation
     *
     * @return string                truncated markup
     */
    public function truncateMarkup(
        string $markup,
        int $length = 400,
        string $appendix = '…',
        bool $appendixInside = false,
        bool $wordsafe = false,
        string $delimiter = ' '
    ): string {
        // immediately return if empty
        if (trim($markup) === '') {
            return $markup;
        }

        // immediately return if truncated text length is 0
        if ($length === 0) {
            return $appendixInside ? '' : $appendix;
        }

        $truncated = '';
        $lengthOutput = 0;
        $position = 0;
        $tags = [];

        // check for existing entities
        $hasEntities = preg_match(self::ENTITIES_PATTERN, $markup);

        // just return the markup if text does not need be truncated
        if (mb_strlen(trim(strip_tags($markup))) <= $length) {
            return $markup;
        }

        // to avoid UTF-8 multibyte glitches we need entities,
        // but no special characters for tags or existing entities
        $markup = str_replace(
            ['&lt;', '&gt;', '&amp;'],
            ['<', '>', '&'],
            htmlentities($markup, ENT_NOQUOTES, 'UTF-8')
        );

        // loop thru text
        while (
            $lengthOutput < $length &&
            preg_match(
                self::TAGS_AND_ENTITIES_PATTERN,
                $markup,
                $match,
                PREG_OFFSET_CAPTURE,
                $position
            )
        ) {
            list($tag, $positionTag) = $match[0];

            // add text leading up to the tag or entity
            $text = substr($markup, $position, $positionTag - $position);
            if ($lengthOutput + mb_strlen($text) > $length) {
                $truncated .= mb_substr($text, 0, $length - $lengthOutput);
                $lengthOutput = $length;
                break;
            }
            $truncated .= $text;
            $lengthOutput += mb_strlen($text);

            // add tags and entities
            if ($tag[0] === '&') {
                // handle the entity...
                $truncated .= $tag;
                // ... which is only one character
                $lengthOutput++;
            } else {
                // handle the tag
                $tagName = $match[1][0];
                if ($tag[1] === '/') {
                    // this is a closing tag
                    $openingTag = array_pop($tags);
                    // check that tags are properly nested
                    assert($openingTag === $tagName);
                    $truncated .= $tag;
                } elseif ($tag[mb_strlen($tag) - 2] === '/') {
                    // self-closing tag in XML dialect
                    $truncated .= $tag;
                } elseif (in_array($tagName, self::SELF_CLOSING_TAGS)) {
                    // self-closing tag in non-XML dialect
                    $truncated .= $tag;
                } else {
                    // opening tag
                    $truncated .= $tag;
                    $tags[] = $tagName;
                }
            }

            // continue after the tag
            $position = $positionTag + mb_strlen($tag);
        }

        // add any remaining text
        if ($lengthOutput < $length && $position < mb_strlen($markup)) {
            $truncated .= mb_substr($markup, $position, $length - $lengthOutput);
        }

        // if the words shouldn't be cut in the middle...
        if ($wordsafe) {
            // ... search the last occurance of the delimiter...
            $spacepos = mb_strrpos($truncated, $delimiter);
            if (isset($spacepos)) {
                // ... and cut the text in this position
                $truncated = mb_substr($truncated, 0, $spacepos);

                // After wordsafe truncation, we need to ensure we don't have incomplete tags
                // Check if we cut in the middle of a tag by counting < and >
                $lastOpenBracket = mb_strrpos($truncated, '<');
                $lastCloseBracket = mb_strrpos($truncated, '>');

                // If the last < comes after the last >, we have an incomplete tag
                if ($lastOpenBracket !== false && ($lastCloseBracket === false || $lastOpenBracket > $lastCloseBracket)) {
                    // Remove the incomplete tag and everything after it
                    $truncated = mb_substr($truncated, 0, $lastOpenBracket);

                    // Trim any trailing whitespace
                    $truncated = rtrim($truncated);

                    // Clear the tags array since we need to recalculate
                    $tags = [];

                    // Re-parse the clean truncated text to find which tags are still open
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
                        list($tag, $positionTag) = $match[0];

                        if ($tag[0] !== '&') {
                            $tagName = $match[1][0];
                            if (mb_strlen($tag) > 1 && $tag[1] === '/') {
                                // Closing tag
                                array_pop($tags);
                            } elseif (!(mb_strlen($tag) >= 2 && $tag[mb_strlen($tag) - 2] === '/' || in_array($tagName, self::SELF_CLOSING_TAGS))) {
                                // Opening tag (not self-closing)
                                $tags[] = $tagName;
                            }
                        }

                        $tempPosition = $positionTag + mb_strlen($tag);
                    }
                }
            }
        }

        // add appendix to last tag content
        if ($appendixInside) {
            $truncated .= $appendix;
        }

        // close any open tags
        while (!empty($tags)) {
            $truncated .= sprintf('</%s>', array_pop($tags));
        }

        // decode entities again if markup did not contain any entities
        if ($hasEntities === 0) {
            $truncated = html_entity_decode($truncated, ENT_COMPAT, 'UTF-8');
        }

        if ($appendixInside) {
            return $truncated;
        } else {
            return $truncated . $appendix;
        }
    }
}
