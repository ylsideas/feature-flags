<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rules([
        \Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector::class,
        \Rector\PHPUnit\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector::class,
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        \Rector\Set\ValueObject\SetList::PHP_80,
        \Rector\PHPUnit\Set\PHPUnitLevelSetList::UP_TO_PHPUNIT_90,
        \Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_90,

    ]);
};
