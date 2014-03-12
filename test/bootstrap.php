<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!is_dir('build')) {
    mkdir('build');
}

$loader = require __DIR__ . '/../vendor/autoload.php';

// Register the annotations
AnnotationRegistry::registerAutoloadNamespaces([
    'Doxport\Annotation' => $loader->getPrefixes()['Doxport\\'][0]
]);

// Load the tests directory into the main namespace
$loader->add('Doxport\\', __DIR__);
