<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Per-DBMS driver packages (yiisoft/db-mysql, db-pgsql, ...) are intentionally not required anywhere in
    // composer.json: each CI job (see .github/workflows/{mysql,pgsql,mssql,oracle,sqlite}.yml) installs only the
    // one it needs via a separate `composer require` step before running that DBMS's test suite.
    ->ignoreUnknownClassesRegex('#^Yiisoft\\\\Db\\\\(Mssql|Mysql|Oracle|Pgsql|Sqlite)\\\\#');
