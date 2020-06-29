<?php
declare(strict_types=1);

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
}
