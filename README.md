# Shorten

> Provides additional truncation functions in PHP.

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

```php
truncateMarkup(
    string $markup,
    int $length = 400,
    string $appendix = '…',
    bool $appendixInside = false,
    bool $wordsafe = false
): string
```

* String `$markup`: Text containing markup
* Integer `$length`: Maximum length of truncated text (default: `400`)
* String `$appendix`: Text added after truncated text (default: `'…'`)
* Boolean `$appendixInside`: Add appendix to last content in tags, increases `$length` by 1 (default: `false`)
* Boolean `$wordsafe`: Wordsafe truncation (default: `false`)
* String `$delimiter`: Delimiter for wordsafe truncation (default: `' '`)

## License

MIT © [Marc Görtz](https://marcgoertz.de/)
