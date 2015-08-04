<?php

// Get global config.php settings
if (!($settings = include(__DIR__ . '/vendor/ezsystems/ezpublish-kernel/config.php'))) {
    throw new \RuntimeException('Could not find config.php, please copy config.php-DEVELOPMENT to vendor/ezsystems/ezpublish-kernel/config.php & customize to your needs!');
}

require_once __DIR__ . '/vendor/autoload.php';
