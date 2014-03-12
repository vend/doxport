<?php

if (!is_dir('build')) {
    mkdir('build');
}

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Doxport\\', __DIR__);
