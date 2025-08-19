# Shorten

> Safely truncate HTML markup while preserving tags, handling entities, and supporting Unicode/emoji with optional word-safe truncation.

[![Test](https://github.com/mrcgrtz/php-shorten/actions/workflows/test.yml/badge.svg)](https://github.com/mrcgrtz/php-shorten/actions/workflows/test.yml)
[![Coverage Status](https://coveralls.io/repos/github/mrcgrtz/php-shorten/badge.svg?branch=main)](https://coveralls.io/github/mrcgrtz/php-shorten?branch=main)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/marcgoertz/shorten)
![Packagist Downloads](https://img.shields.io/packagist/dt/marcgoertz/shorten)
![Packagist Stars](https://img.shields.io/packagist/stars/marcgoertz/shorten)
[![MIT License](https://img.shields.io/github/license/mrcgrtz/php-shorten)](https://github.com/mrcgrtz/php-shorten/blob/main/LICENSE.md)

## Installation

I recommend using [Composer](https://getcomposer.org/) for installing and using Shorten:

```bash
composer require marcgoertz/shorten
```

Of course you can also just require it in your scripts directly.

## Usage

```php
<?php

use Marcgoertz\Shorten\Shorten;

$shorten = new Shorten();
print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10);
?>
```

Output:

```html
<a href="https://example.com/">Go to exam</a>…
```

## Functions

### `truncateMarkup()`

```php
truncateMarkup(
    string $markup,
    int $length = 400,
    string $appendix = '…',
    bool $appendixInside = false,
    bool $wordsafe = false,
    string $delimiter = ' '
): string
```

#### Parameters

* `string $markup`: Text containing markup
* `int $length`: Maximum length of truncated text (default: `400`)
* `string $appendix`: Text added after truncated text (default: `'…'`)
* `bool $appendixInside`: Add appendix to last content in tags, increases `$length` by 1 (default: `false`)
* `bool $wordsafe`: Wordsafe truncation, cuts at word boundaries (default: `false`)
* `string $delimiter`: Delimiter for wordsafe truncation (default: `' '`)

#### Examples

```php
<?php
use Marcgoertz\Shorten\Shorten;

$shorten = new Shorten();

// Basic truncation
$result = $shorten->truncateMarkup('<b>Hello world test</b>', 10);
// Output: <b>Hello worl</b>…

// Appendix inside tags
$result = $shorten->truncateMarkup('<b>Hello world test</b>', 10, '...', true);
// Output: <b>Hello worl...</b>

// Wordsafe truncation (cuts at word boundaries)
$result = $shorten->truncateMarkup('<b>Hello world test</b>', 10, '...', false, true);
// Output: <b>Hello</b>...

// Custom delimiter for wordsafe truncation
$result = $shorten->truncateMarkup('<b>Hello-world-test</b>', 10, '...', false, true, '-');
// Output: <b>Hello</b>...

// Preserves HTML structure with nested tags
$result = $shorten->truncateMarkup('<div><b><i>Hello world</i></b></div>', 8);
// Output: <div><b><i>Hello wo</i></b></div>…

// Handles HTML entities correctly
$result = $shorten->truncateMarkup('<b>Caf&eacute; &amp; Restaurant</b>', 8);
// Output: <b>Café &amp; Re</b>…
?>
```

#### Features

* ✅ Preserves HTML tag structure and proper nesting
* ✅ Handles HTML entities correctly
* ✅ Supports self-closing tags (both XML and HTML5 style)
* ✅ UTF-8 and multibyte character support (including emojis)
* ✅ Wordsafe truncation to avoid cutting words in the middle
* ✅ Configurable appendix text and placement

## License

MIT © [Marc Görtz](https://marcgoertz.de/)
