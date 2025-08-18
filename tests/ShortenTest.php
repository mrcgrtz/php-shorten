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

    public function testTruncateWithDifferentAppendix(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<p>Hello world...</p>',
            $shorten->truncateMarkup('<p>Hello world test</p>', 11, '...', true)
        );
    }

    public function testTruncateWithNoAppendix(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<p>Hello</p>',
            $shorten->truncateMarkup('<p>Hello world</p>', 5, '', true)
        );
    }

    public function testTruncateWithZeroLength(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '…',
            $shorten->truncateMarkup('<p>Hello world</p>', 0)
        );
    }

    public function testTruncateWithNegativeLength(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '…',
            $shorten->truncateMarkup('<p>Hello world</p>', -5)
        );
    }

    public function testTruncateEmptyString(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '',
            $shorten->truncateMarkup('', 10)
        );
    }

    public function testTruncateWithNestedTags(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<div><p><strong>Hello</strong></p></div>…',
            $shorten->truncateMarkup('<div><p><strong>Hello world test</strong></p></div>', 5)
        );
    }

    public function testTruncateWithOnlyTags(): void
    {
        $shorten = new Shorten();
        $this->assertEquals(
            '<div><span></span></div>',
            $shorten->truncateMarkup('<div><span></span></div>', 10)
        );
    }

    public function testTruncateWithVeryLongText(): void
    {
        $shorten = new Shorten();
        $longText = '<p>' . str_repeat('Lorem ipsum dolor sit amet ', 100) . '</p>';
        $result = $shorten->truncateMarkup($longText, 50);
        $this->assertLessThan(mb_strlen($longText), mb_strlen($result));
        $this->assertStringEndsWith('…', strip_tags($result));
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

    public function testParameterValidation(): void
    {
        $shorten = new Shorten();

        // Test negative length
        $this->assertEquals(
            '…',
            $shorten->truncateMarkup('<p>Hello world</p>', -5)
        );

        // Test zero length
        $this->assertEquals(
            '…',
            $shorten->truncateMarkup('<p>Hello world</p>', 0)
        );

        // Test zero length with appendix inside
        $this->assertEquals(
            '',
            $shorten->truncateMarkup('<p>Hello world</p>', 0, '...', true)
        );
    }

    public function testEmptyDelimiterThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delimiter cannot be empty for wordsafe truncation');

        $shorten = new Shorten();
        $shorten->truncateMarkup('<p>Hello world</p>', 10, '...', false, true, '');
    }

    public function testMalformedHtmlHandling(): void
    {
        $shorten = new Shorten();

        // Test mismatched closing tag - should handle gracefully
        $result = $shorten->truncateMarkup('<div><p>Hello</div></p>', 10);
        $this->assertStringContainsString('Hello', $result);

        // Test unclosed tag
        $result = $shorten->truncateMarkup('<div><p>Hello world', 8);
        $this->assertEquals('<div><p>Hello wo</p></div>…', $result);
    }
}
