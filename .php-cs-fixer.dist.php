<?php

// Make sure to adjust the lint-staged command in package.json if these are changed.
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'tests')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        'phpdoc_summary' => false,
        'ordered_imports' => false,
    ])
    ->setFinder($finder)
;