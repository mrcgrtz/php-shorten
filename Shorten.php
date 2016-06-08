<?php
/**
 * Provides truncation functions.
 *
 * <code>
 * <?php
 * require_once('Shorten.php');
 * $shorten = new \Marcgoertz\Shorten\Shorten;
 * $shorten->truncateMarkup('<a href="http://example.com/">Go to example site</a>', 10);
 * ?>
 * </code>
 *
 * @package   php-shorten
 * @example   example.html.php
 * @link      https://github.com/Dreamseer/php-shorten/
 * @author    Marc Görtz (http://marcgoertz.de/)
 * @license   MIT License
 * @copyright Copyright (c) 2011-2015, Marc Görtz
 * @version   2.1.0
 */
namespace Marcgoertz\Shorten;

class Shorten
{

	const VERSION = '2.1.0';

	/**
	 * Safely truncate text containing markup.
	 *
	 * @param   string $markup         text containing markup
	 * @param   int    $length         maximum length of truncated text (default: 400)
	 * @param   string $appendix       text added after truncated text (default: '…')
	 * @param   bool   $appendixInside add appendix to last content in tags, increases $length by 1 (default: false)
	 * @param   bool   $wordsafe       wordsafe truncation (default: false)
	 * @param   string $delimiter      delimiter for wordsafe truncation (default: ' ')
	 * @return  string                 truncated markup
	 */
	public function truncateMarkup($markup, $length = 400, $appendix = '…', $appendixInside = FALSE, $wordsafe = FALSE, $delimiter = ' ')
	{
		$truncated = '';
		$lengthOutput = 0;
		$position = 0;
		$tags = array();

		// just return the markup if text does not need be truncated
		if (strlen(trim(strip_tags($markup))) <= $length) {
			return $markup;
		}

		// to avoid UTF-8 multibyte glitches we need entities, but no special characters for tags or existing entities
		$markup = str_replace(array(
			'&lt;', '&gt;', '&amp;',
		), array(
			'<', '>', '&',
		), htmlentities($markup, ENT_NOQUOTES, 'UTF-8'));

		// loop thru text
		while ($lengthOutput < $length && preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $markup, $match, PREG_OFFSET_CAPTURE, $position)) {
			list($tag, $positionTag) = $match[0];

			// add text leading up to the tag or entity
			$text = substr($markup, $position, $positionTag - $position);
			if ($lengthOutput + strlen($text) > $length) {
				$truncated .= substr($text, 0, $length - $lengthOutput);
				$lengthOutput = $length;
				break;
			}
			$truncated .= $text;
			$lengthOutput += strlen($text);

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
				} else if ($tag[strlen($tag) - 2] === '/') {
					// self-closing tag in XML dialect
					$truncated .= $tag;
				} else {
					// opening tag
					$truncated .= $tag;
					$tags[] = $tagName;
				}
			}

			// continue after the tag
			$position = $positionTag + strlen($tag);
		}

		// add any remaining text
		if ($lengthOutput < $length && $position < strlen($markup)) {
			$truncated .= substr($markup, $position, $length - $lengthOutput);
		}

		// if the words shouldn't be cut in the middle...
		if ($wordsafe) {
			// ... search the last occurance of the delimiter...
			$spacepos = strrpos($truncated, $delimiter);
			if (isset($spacepos)) {
				// ... and cut the text in this position
				$truncated = substr($truncated, 0, $spacepos);
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

		if ($appendixInside) {
			return $truncated;
		} else {
			return $truncated . $appendix;
		}

	}

}
