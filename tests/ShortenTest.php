<?php

declare(strict_types=1);

namespace Marcgoertz\Shorten;

use PHPUnit\Framework\TestCase;
use Marcgoertz\Shorten\Shorten;

final class ShortenTest extends TestCase
{
    public function testTruncatesMarkup(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<a href="https://example.com/">Go to exam</a>…',
            $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10)
        );
    }

    public function testTruncatesMarkupWithAppendixOutside(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<a href="https://example.com/">Go to</a>…',
            $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', false, true)
        );
    }

    public function testTruncatesMarkupWithAppendixInside(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<a href="https://example.com/">Go to…</a>',
            $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', true, true)
        );
    }

    public function testTruncatesMarkupOnlyIfNeeded(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            'Lorem ipsum <b>dolor</b> sit amet',
            $shorten->truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26)
        );
    }

    public function testTruncatesMarkupWithEntities(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<p>PHP &eacute;l&eacute;…</p>',
            $shorten->truncateMarkup('<p>PHP &eacute;l&eacute;phant</p>', 7, '…', true)
        );
    }

    public function testTruncatesMarkupWithUnicodeChars(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<p>PHP élé…</p>',
            $shorten->truncateMarkup('<p>PHP éléphant</p>', 7, '…', true)
        );
    }

    public function testTruncatesMarkupWithEmoji(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<p>PHP élé…</p>',
            $shorten->truncateMarkup('<p>PHP éléphant 🐘</p>', 7, '…', true)
        );
        $this->assertEquals(
            '<p>PHP 🐘 é…</p>',
            $shorten->truncateMarkup('<p>PHP 🐘 éléphant 🐘</p>', 7, '…', true)
        );
        $this->assertEquals(
            '<p>PHP …</p>',
            $shorten->truncateMarkup('<p>PHP 🐘 éléphant 🐘</p>', 4, '…', true)
        );
    }

    public function testTruncatesMarkupWithXMLStyledSelfClosingTags(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<a href="https://example.com/">Go to<br />examp</a>…',
            $shorten->truncateMarkup('<a href="https://example.com/">Go to<br />example site</a>', 10)
        );
    }

    public function testTruncatesMarkupWithNonXMLStyledSelfClosingTags(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<a href="https://example.com/"><img src="icon.gif" alt=""> Go to exa</a>…',
            $shorten->truncateMarkup('<a href="https://example.com/"><img src="icon.gif" alt=""> Go to example site</a>', 10)
        );
    }

    public function testTruncatesMarkupWithHeadingTags(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<h1>Example</h1>…',
            $shorten->truncateMarkup('<h1>Example Heading</h1>', 7)
        );
    }

    public function testTruncatesMarkupWithDelimiterInTag(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            'Hello world <a href="#" rel="nofollow">li...</a>',
            $shorten->truncateMarkup('Hello world <a href="#" rel="nofollow">link</a>', 14, '...', true, false)
        );
    }

    public function testTruncatesMarkupWithDelimiterInTagWordSafe(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            'Hello world...',
            $shorten->truncateMarkup('Hello world <a href="#" rel="nofollow">link</a>', 14, '...', true, true)
        );
    }
}
