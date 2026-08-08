<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // Per-DBMS driver packages (yiisoft/db-mysql, db-pgsql, ...) are intentionally not required anywhere in
    // composer.json: each CI job (see .github/workflows/{mysql,pgsql,mssql,oracle,sqlite}.yml) installs only the
    // one it needs via a separate `composer require` step before running that DBMS's test suite.
    ->ignoreUnknownClassesRegex('#^Yiisoft\\\\Db\\\\(Mssql|Mysql|Oracle|Pgsql|Sqlite)\\\\#')
    // ext-pdo is a genuine runtime requirement (all supported DB drivers connect through PDO), but it isn't tied
    // to any PDO symbol referenced directly in src/ — only test support factories touch the PDO class itself.
    ->ignoreErrorsOnExtension('ext-pdo', [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV]);
