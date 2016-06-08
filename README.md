# Shorten

Provides additional truncation functions in PHP.

```php
<?php
require_once('Shorten.php');
$shorten = new Shorten;
$shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10);
?>
```

Output:

```html
<a href="https://example.com/">Go to exam</a>…
```

## Functions

	truncateMarkup($markup, $length = 400, $appendix = '…', $appendixInside = FALSE, $wordsafe = FALSE)

 * String `$markup`: Text containing markup
 * Integer `$length`: Maximum length of truncated text (default: 400)
 * String `$appendix`: Text added after truncated text (default: '…')
 * Boolean `$appendixInside`: Add appendix to last content in tags, increases $length by 1 (default: false)
 * Boolean `$wordsafe`: Wordsafe truncation (default: false)
 * String `$delimiter`: Delimiter for wordsafe truncation (default: ' ')

## License

Copyright (c) 2011–2016 Marc Görtz, https://marcgoertz.de/

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
