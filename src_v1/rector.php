<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/classes',
        __DIR__ . '/common',
        __DIR__ . '/controllers',
        __DIR__ . '/cron',
        __DIR__ . '/db',
        __DIR__ . '/dynamic-javascript',
        __DIR__ . '/helperclasses',
        __DIR__ . '/includes',
        __DIR__ . '/old_core',
        __DIR__ . '/public',
        __DIR__ . '/routes',
        __DIR__ . '/views',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ]);
