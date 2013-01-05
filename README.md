# Shorten

Provides additional truncation functions in PHP.

```php
<?php
require_once('Shorten.php');
$shorten = new Shorten;
$shorten->truncateMarkup('<a href="http://example.com/">Go to example site</a>', 10);
?>
```

Output:

```html
<a href="http://example.com/">Go to exam</a>…
```

## Functions

	truncateMarkup($markup, $length = 400, $appendix = '…', $appendixInside = FALSE, $wordsafe = FALSE)

 * String `$markup`: Text containing markup
 * Integer `$length`: Maximum length of truncated text (default: 400)
 * String `$appendix`: Text added after truncated text (default: '…')
 * Boolean `$appendixInside`: Add appendix to last content in tags, increases $length by 1 (default: false)
 * Boolean `$wordsafe`: Wordsafe truncation (default: false)

## License

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE Version 2, December 2004

Copyright (C) 2004 Sam Hocevar sam@hocevar.net

Everyone is permitted to copy and distribute verbatim or modified copies of this license document, and changing it is allowed as long as the name is changed.

DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

You just DO WHAT THE FUCK YOU WANT TO.
