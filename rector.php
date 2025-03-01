<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // uncomment to reach your current PHP version
     ->withPhpSets(php82: true)
    ->withTypeCoverageLevel(1024)
    ->withDeadCodeLevel(1024)
    ->withCodeQualityLevel(1024);
