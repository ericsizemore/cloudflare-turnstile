<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitSelfCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withCache(
        __DIR__ . '/build/rector'
    )
    ->withComposerBased(phpunit: true)
    ->withRules([
        //AssertEqualsToSameRector::class,
        PreferPHPUnitSelfCallRector::class,
    ])
    ->withSkip([
        JoinStringConcatRector::class,
        PreferPHPUnitThisCallRector::class,
    ])
    ->withPhpSets(
        php83: true
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        rectorPreset: true,
        phpunitCodeQuality: true,
    )
;
