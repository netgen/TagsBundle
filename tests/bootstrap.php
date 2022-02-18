<?php

declare(strict_types=1);

$kernelDir = __DIR__ . '/../vendor/ibexa/core';

// Get global config.php settings
if (!\file_exists($kernelDir . '/config.php')) {
    if (!\symlink(__DIR__ . '/config.php-DEVELOPMENT', $kernelDir . '/config.php')) {
        throw new \RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!');
    }
}

if (!($settings = include($kernelDir . '/config.php'))) {
    throw new \RuntimeException('Could not read config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!');
}

require_once __DIR__ . '/../vendor/autoload.php';
