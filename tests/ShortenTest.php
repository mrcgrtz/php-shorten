<?php

declare(strict_types=1);

namespace Marcgoertz\Shorten;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ShortenTest extends TestCase
{
    private Shorten $shorten;

    protected function setUp(): void
    {
        $this->shorten = new Shorten();
    }

    // === Basic Functionality Tests ===

    public function testBasicTruncation(): void
    {
        $result = $this->shorten->truncateMarkup(
            '<a href="https://example.com/">Go to example site</a>',
            10
        );
        $this->assertEquals('<a href="https://example.com/">Go to exam</a>…', $result);
    }

    public function testAppendixPlacement(): void
    {
        $markup = '<a href="https://example.com/">Go to example site</a>';

        // Appendix outside (default)
        $result = $this->shorten->truncateMarkup($markup, 10, '…', false, true);
        $this->assertEquals('<a href="https://example.com/">Go to</a>…', $result);

        // Appendix inside
        $result = $this->shorten->truncateMarkup($markup, 10, '…', true, true);
        $this->assertEquals('<a href="https://example.com/">Go to…</a>', $result);
    }

    public function testNoTruncationNeeded(): void
    {
        $result = $this->shorten->truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26);
        $this->assertEquals('Lorem ipsum <b>dolor</b> sit amet', $result);
    }

    public function testCustomAppendix(): void
    {
        $result = $this->shorten->truncateMarkup('<p>Hello world test</p>', 11, '...', true);
        $this->assertEquals('<p>Hello world...</p>', $result);
    }

    // === Edge Cases ===

    public function testEdgeCaseLengths(): void
    {
        // Zero length
        $this->assertEquals('…', $this->shorten->truncateMarkup('<p>Hello world</p>', 0));
        $this->assertEquals('', $this->shorten->truncateMarkup('<p>Hello world</p>', 0, '...', true));

        // Negative length
        $this->assertEquals('…', $this->shorten->truncateMarkup('<p>Hello world</p>', -5));

        // Length of 1
        $this->assertEquals('<p>H</p>…', $this->shorten->truncateMarkup('<p>Hello world</p>', 1));

        // Very large length
        $this->assertEquals('<p>Short</p>', $this->shorten->truncateMarkup('<p>Short</p>', 1000000));
    }

    public function testEmptyContent(): void
    {
        $this->assertEquals('', $this->shorten->truncateMarkup('', 10));
        $this->assertEquals('<p>   </p>', $this->shorten->truncateMarkup('<p>   </p>', 10));
    }

    public function testBoundaryConditions(): void
    {
        // Length exactly matches content
        $this->assertEquals('<p>Hello</p>', $this->shorten->truncateMarkup('<p>Hello</p>', 5));
        $this->assertEquals('<strong>Hello</strong>', $this->shorten->truncateMarkup('<strong>Hello</strong>', 5));
    }

    // === HTML Structure Tests ===

    public function testNestedTags(): void
    {
        $result = $this->shorten->truncateMarkup('<div><p><strong>Hello world test</strong></p></div>', 5);
        $this->assertEquals('<div><p><strong>Hello</strong></p></div>…', $result);
    }

    public function testSelfClosingTags(): void
    {
        // XML-style
        $result = $this->shorten->truncateMarkup('<p>Line one<br/>Line two<hr/>End</p>', 15);
        $this->assertEquals('<p>Line one<br/>Line tw</p>…', $result);

        // HTML-style
        $result = $this->shorten->truncateMarkup('<p>Image: <img src="test.jpg" alt="test"> and text</p>', 12);
        $this->assertEquals('<p>Image: <img src="test.jpg" alt="test"> and </p>…', $result);

        // Mixed
        $result = $this->shorten->truncateMarkup('<div>Start<br>Middle<hr/>End</div>', 10);
        $this->assertEquals('<div>Start<br>Middl</div>…', $result);
    }

    public function testComplexAttributes(): void
    {
        $result = $this->shorten->truncateMarkup(
            '<a href="https://example.com?param=value&other=test" title="Link\'s title">Link text here</a>',
            8
        );
        $this->assertEquals(
            '<a href="https://example.com?param=value&other=test" title="Link\'s title">Link tex</a>…',
            $result
        );
    }

    public function testTagsOnly(): void
    {
        $this->assertEquals('<div><span></span></div>', $this->shorten->truncateMarkup('<div><span></span></div>', 10));
    }

    // === Malformed HTML Tests ===

    public function testMalformedHtml(): void
    {
        // Mismatched closing tag
        $result = $this->shorten->truncateMarkup('<div><p>Hello</div></p>', 10);
        $this->assertStringContainsString('Hello', $result);

        // Unclosed tags
        $result = $this->shorten->truncateMarkup('<div><p>Hello world', 8);
        $this->assertEquals('<div><p>Hello wo</p></div>…', $result);

        // Complex nesting with mismatched tags
        $result = $this->shorten->truncateMarkup('<div><p><span><strong>Hello</div></strong></span></p>', 10);
        $this->assertEquals('<div><p><span><strong>Hello</div></strong></span></p>', $result);
    }

    // === Entity Handling Tests ===

    public function testHtmlEntities(): void
    {
        $result = $this->shorten->truncateMarkup('<p>PHP &eacute;l&eacute;phant</p>', 7, '…', true);
        $this->assertEquals('<p>PHP &eacute;l&eacute;…</p>', $result);

        // Multiple entities
        $result = $this->shorten->truncateMarkup('<p>&lt;&gt;&amp;&quot;&apos;</p>', 3);
        $this->assertEquals('<p>&lt;&gt;&amp;</p>…', $result);

        // Mixed with text
        $result = $this->shorten->truncateMarkup('<p>A&nbsp;B&nbsp;C&nbsp;D</p>', 4);
        $this->assertEquals('<p>A&nbsp;B&nbsp;</p>…', $result);

        // Numeric and hex entities
        $result = $this->shorten->truncateMarkup('<p>&#65;&#66;&#67;normal</p>', 5);
        $this->assertEquals('<p>&#65;&#66;&#67;no</p>…', $result);
    }

    // === Unicode and Emoji Tests ===

    public function testUnicodeHandling(): void
    {
        $result = $this->shorten->truncateMarkup('<p>PHP éléphant</p>', 7, '…', true);
        $this->assertEquals('<p>PHP élé…</p>', $result);

        // Mixed scripts
        $result = $this->shorten->truncateMarkup('<p>ASCII тест ελληνικά</p>', 10);
        $this->assertEquals('<p>ASCII тест</p>…', $result);
    }

    public function testEmojiHandling(): void
    {
        $result = $this->shorten->truncateMarkup('<p>PHP éléphant 🐘</p>', 7, '…', true);
        $this->assertEquals('<p>PHP élé…</p>', $result);

        $result = $this->shorten->truncateMarkup('<p>PHP 🐘 éléphant 🐘</p>', 7, '…', true);
        $this->assertEquals('<p>PHP 🐘 é…</p>', $result);

        // Complex emoji with modifiers
        $result = $this->shorten->truncateMarkup('<p>👨‍👩‍👧‍👦 family 👋🏽 wave</p>', 3);
        $this->assertEquals('<p>👨‍👩‍👧‍👦 f</p>…', $result);
    }

    // === Wordsafe Truncation Tests ===

    public function testWordsafeTruncation(): void
    {
        $markup = '<a href="https://example.com/">Go to example site</a>';

        // Regular vs wordsafe
        $regular = $this->shorten->truncateMarkup($markup, 10, '…', false, false);
        $wordsafe = $this->shorten->truncateMarkup($markup, 10, '…', false, true);
        $this->assertEquals('<a href="https://example.com/">Go to exam</a>…', $regular);
        $this->assertEquals('<a href="https://example.com/">Go to</a>…', $wordsafe);
    }

    public function testWordsafeWithDifferentDelimiters(): void
    {
        // Default space delimiter
        $result = $this->shorten->truncateMarkup('<p>Word1   Word2   Word3</p>', 15, '...', true, true, ' ');
        $this->assertEquals('<p>Word1   Word2 ...</p>', $result);

        // Custom delimiters
        $result = $this->shorten->truncateMarkup('<p>One,Two,Three,Four</p>', 12, '...', true, true, ',');
        $this->assertEquals('<p>One,Two...</p>', $result);

        $result = $this->shorten->truncateMarkup('<p>One-Two-Three-Four</p>', 12, '...', true, true, '-');
        $this->assertEquals('<p>One-Two...</p>', $result);
    }

    public function testWordsafeEdgeCases(): void
    {
        // No delimiter found
        $result = $this->shorten->truncateMarkup('<p>Supercalifragilisticexpialidocious</p>', 10, '...', true, true);
        $this->assertEquals('<p>Supercalif...</p>', $result);

        // Delimiter at end
        $result = $this->shorten->truncateMarkup('<p>Hello world </p>', 12, '...', true, true);
        $this->assertEquals('<p>Hello world </p>', $result);

        // With incomplete tags
        $result = $this->shorten->truncateMarkup('<p>Hello <strong>world test</strong></p>', 10, '...', true, true);
        $this->assertEquals('<p>Hello...</p>', $result);
    }

    // === Parameter Validation Tests ===

    public function testParameterValidation(): void
    {
        // Negative and zero lengths are handled
        $this->assertEquals('…', $this->shorten->truncateMarkup('<p>Hello world</p>', -5));
        $this->assertEquals('…', $this->shorten->truncateMarkup('<p>Hello world</p>', 0));
    }

    public function testEmptyDelimiterException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delimiter cannot be empty for wordsafe truncation');

        $this->shorten->truncateMarkup('<p>Hello world</p>', 10, '...', false, true, '');
    }

    // === Performance and Complex Content Tests ===

    public function testPerformanceWithLargeContent(): void
    {
        // Large content with many tags
        $largeTags = str_repeat('<span>word</span> ', 1000);
        $result = $this->shorten->truncateMarkup("<div>{$largeTags}</div>", 50);
        $this->assertLessThan(250, mb_strlen($result));

        // Performance test
        $largeText = '<p>'.str_repeat('Lorem ipsum dolor sit amet. ', 500).'</p>';
        $start = microtime(true);
        $result = $this->shorten->truncateMarkup($largeText, 100);
        $end = microtime(true);
        $this->assertLessThan(1.0, $end - $start);
        $this->assertStringEndsWith('…', $result);
    }

    public function testVeryLongContent(): void
    {
        $longText = '<p>'.str_repeat('Lorem ipsum dolor sit amet ', 100).'</p>';
        $result = $this->shorten->truncateMarkup($longText, 50);
        $this->assertLessThan(mb_strlen($longText), mb_strlen($result));
        $this->assertStringEndsWith('…', strip_tags($result));
    }

    public function testWhitespaceHandling(): void
    {
        // Leading/trailing whitespace
        $result = $this->shorten->truncateMarkup('<p>   Hello\nworld test</p>', 8);
        $this->assertEquals('<p>   Hello</p>…', $result);

        // Tabs and newlines
        $result = $this->shorten->truncateMarkup("<p>\tHello\nworld\r\ntest</p>", 10);
        $this->assertEquals("<p>\tHello\nwor</p>…", $result);
    }

    // === Complex Real-World Scenarios ===

    public function testMixedContentScenarios(): void
    {
        // Tags, entities, unicode, emoji combined
        $markup = '<p>Hello &amp; 世界 🌍 <strong>bold &lt;text&gt;</strong> normal 🚀</p>';
        $result = $this->shorten->truncateMarkup($markup, 15, '⋯', true);
        $this->assertEquals('<p>Hello &amp; 世界 🌍 <strong>bo⋯</strong></p>', $result);

        // Real-world complex example
        $markup
            = '<article><h2>Title éléphant 🐘</h2>'
            .'<p>Paragraph with <a href="#">link &amp; more</a> content.</p></article>';
        $result = $this->shorten->truncateMarkup($markup, 20, '…', false, true);
        $this->assertEquals('<article><h2>Title &eacute;l&eacute;phant</h2></article>…', $result);
    }

    public function testDeepNestingWithAppendix(): void
    {
        $result = $this->shorten->truncateMarkup(
            '<div><section><article><p>Content</p></article></section></div>',
            5,
            '***',
            true
        );
        $this->assertEquals('<div><section><article><p>Conte***</p></article></section></div>', $result);
    }

    public function testComplexTagRecalculation(): void
    {
        $markup = '<div><p><strong>Word1 Word2</strong> <em>Word3 Word4</em></p></div>';
        $result = $this->shorten->truncateMarkup($markup, 12, '...', true, true);
        $this->assertEquals('<div><p><strong>Word1 Word2</strong>...</p></div>', $result);
    }
}
