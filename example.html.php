<?php

use Marcgoertz\Shorten\Shorten;

require_once 'Shorten.php';
$shorten = new Shorten();
?>
<!doctype html>
<meta charset=utf-8>
<title>Shorten examples</title>
<p>
    <?php
        // provides: <a href="https://example.com/">Go to exam</a>…
        echo $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10);
?>

<p>
    <?php
    // provides: <a href="https://example.com/">Go to</a>…
    echo $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', false, true);
?>

<p>
    <?php
    // provides: <a href="https://example.com/">Go to…</a>
    echo $shorten->truncateMarkup('<a href="https://example.com/">Go to example site</a>', 10, '…', true, true);
?>

<p>
    <?php
    // provides: Lorem ipsum <b>dolor</b> sit amet
    echo $shorten->truncateMarkup('Lorem ipsum <b>dolor</b> sit amet', 26);
?>
