<?php
declare(strict_types=1);

use Marcgoertz\Shorten\Shorten;
use PHPUnit\Framework\TestCase;

final class ShortenTest extends TestCase
{
    public function testTruncatesMarkup(): void
    {
        $this->assertEquals(
            '<a href="https://example.com/">Go to exam</a>…',
            Shorten::truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10)
        );
    }

    public function testTruncatesMarkupWithAppendixOutside(): void
    {
        $this->assertEquals(
            '<a href="https://example.com/">Go to</a>…',
            Shorten::truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', FALSE, TRUE)
        );
    }

    public function testTruncatesMarkupWithAppendixInside(): void
    {
        $this->assertEquals(
            '<a href="https://example.com/">Go to…</a>',
            Shorten::truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', TRUE, TRUE)
        );
    }

    public function testTruncatesMarkupOnlyIfNeeded(): void
    {
        $this->assertEquals(
            'Lorem ipsum <b>dolor</b> sit amet',
            Shorten::truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26)
        );
    }
}


