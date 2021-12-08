<?php
require_once 'Shorten.php';
$shorten = new \Marcgoertz\Shorten\Shorten();
?>
<!doctype html>
<meta charset=utf-8>
<title>Shorten examples</title>
<p>
    <?php
        // provides: <a href="https://example.com/">Go to exam</a>…
        print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10);
    ?>

<p>
    <?php
        // provides: <a href="https://example.com/">Go to</a>…
        print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', false, true);
    ?>

<p>
    <?php
        // provides: <a href="https://example.com/">Go to…</a>
        print $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', true, true);
    ?>

<p>
    <?php
        // provides: Lorem ipsum <b>dolor</b> sit amet
        print $shorten->truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26);
    ?>
