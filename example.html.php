<!DOCTYPE html>
<?php
require_once('Shorten.php');
$shorten = new \Marcgoertz\Shorten\Shorten;
?>
<p>
	<?php
		// provides: <a href="https://example.com/">Go to exam</a>…
		print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10);
	?>
</p>

<p>
	<?php
		// provides: <a href="https://example.com/">Go to</a>…
		print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', FALSE, TRUE);
	?>
</p>

<p>
	<?php
		// provides: <a href="https://example.com/">Go to…</a>
		print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', TRUE, TRUE);
	?>
</p>

<p>
	<?php
		// provides: Lorem ipsum <b>dolor</b> sit amet
		print $shorten->truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26);
	?>
</p>
