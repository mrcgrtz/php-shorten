<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()->in(__DIR__ . '/');

$config = new Config();

return $config
	->setRules([
		'@PSR12' => true,
		'@PHP84Migration' => true,
		'@PhpCsFixer' => true,
	])
	->setFinder($finder)
;
