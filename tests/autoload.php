<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../app/autoload.php';

// Ignore PHPUnit annotations. Otherwise they'd trigger errors, because Symfony does not know about them.
AnnotationReader::addGlobalIgnoredName('group');
AnnotationReader::addGlobalIgnoredName('dataProvider');

return $loader;
