<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii"  >
    </a>
    <h1 align="center">Yii Logging Library - DB Target</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/log-target-db/v/stable.png)](https://packagist.org/packages/yiisoft/log-target-db)
[![Total Downloads](https://poser.pugx.org/yiisoft/log-target-db/downloads.png)](https://packagist.org/packages/yiisoft/log-target-db)
[![codecov](https://codecov.io/gh/yiisoft/log-target-db/branch/master/graph/badge.svg?token=AP7VK8ZYIF)](https://codecov.io/gh/yiisoft/log-target-db)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Flog-target-db%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/log-target-db/master)
[![static analysis](https://github.com/yiisoft/log-target-db/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/log-target-db/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/log-target-db/coverage.svg)](https://shepherd.dev/github/yiisoft/log-target-db)

This package provides the Database target for the [yiisoft/log](https://github.com/yiisoft/log) library.

## Supported databases

| Packages                                                  | PHP           | Versions         | CI-Actions                                                                                                                                                          |
|-----------------------------------------------------------|---------------|------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [[db-mssql]](https://github.com/yiisoft/db-mssql)         | **8.1 - 8.3** | **2017 - 2025**  | [![mssql](https://github.com/yiisoft/log-target-db/actions/workflows/mssql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/mssql.yml)    | |
| [[db-mysql]](https://github.com/yiisoft/db-mysql) (MySQL)   | **8.1 - 8.3** | **5.7 - 9.5**    | [![mysql](https://github.com/yiisoft/log-target-db/actions/workflows/mysql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/mysql.yml)    |
| [[db-mysql]](https://github.com/yiisoft/db-mysql) (MariaDB) | **8.1 - 8.3** | **10.4 - 12.10** | [![mariadb](https://github.com/yiisoft/log-target-db/actions/workflows/mariadb.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/mariadb.yml)  |
| [[db-oracle]](https://github.com/yiisoft/db-oracle)       | **8.1 - 8.3** | **11C - 21C**    | [![oracle](https://github.com/yiisoft/log-target-db/actions/workflows/oracle.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/oracle.yml) |
| [[db-pgsql]](https://github.com/yiisoft/db-pgsql)         | **8.1 - 8.3** | **9.0 - 18.0**   | [![pgsql](https://github.com/yiisoft/log-target-db/actions/workflows/pgsql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/pgsql.yml)    |
| [[db-sqlite]](https://github.com/yiisoft/db-sqlite)       | **8.1 - 8.3** | **3:latest**     | [![sqlite](https://github.com/yiisoft/log-target-db/actions/workflows/sqlite.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/sqlite.yml) |

## Requirements

- PHP 8.1 or higher.
- `PDO` PHP extension.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/log-target-db
```

## Create database connection

For more information see [yiisoft/db](https://github.com/yiisoft/db/tree/master/docs/guide/en#create-connection).

## Database Preparing

Package provides two way for preparing database:

1. Raw SQL. You can use it with the migration package used in your application.

    - Ensure tables:
        - [MSSQL](sql/sqlsrv-up.sql),
        - [MySQL / MariaDB](sql/mysql-up.sql),
        - [Oracle](sql/oci-up.sql),
        - [PostgreSQL](sql/pgsql-up.sql)
        - [SQLite](sql/sqlite-up.sql)

    - Ensure no tables:
        - [MSSQL](sql/sqlsrv-down.sql),
        - [MySQL / MariaDB](sql/mysql-down.sql),
        - [Oracle](sql/oci-down.sql),
        - [PostgreSQL](sql/pgsql-down.sql)
        - [SQLite](sql/sqlite-down.sql)

2. `DbSchemaManager` for `ensureTable()`, `ensureNoTable()` methods for log table (by default `{{%yii_log}}`).

```php
// Create db schema manager
$dbSchemaManager = new DbSchemaManager($db);

// Ensure table with default name
$dbSchemaManager->ensureTable();

// Ensure table with custom name
$dbSchemaManager->ensureTable('{{%custom_log_table}}');

// Ensure no table with default name
$dbSchemaManager->ensureNoTable();

// Ensure no table with custom name
$dbSchemaManager->ensureNoTable('{{%custom_log_table}}');
```

## General usage

When creating an instance of `\Yiisoft\Log\Logger`, you must pass an instance of the database connection.

Creating a target:

```php
$dbTarget = new \Yiisoft\Log\Target\Db\DbTarget($db, $table, $levels);
```

- `$db (\Yiisoft\Db\Connection\ConnectionInterface)` - The database connection instance.
- `$table (string)` - The name of the database table to store the log messages. Defaults to "{{%yii_log}}".
- `$levels (array)` - Optional. The log message levels that this target is interested in. Defaults to empty array (all levels). Example: `[\Psr\Log\LogLevel::ERROR, \Psr\Log\LogLevel::WARNING]`.

Creating a logger:

```php
$logger = new \Yiisoft\Log\Logger([$dbTarget]);
```

You can filter which log levels are stored in the database by passing the `$levels` parameter to the constructor:

```php
use Psr\Log\LogLevel;

// Only store ERROR and WARNING level messages
$dbTarget = new \Yiisoft\Log\Target\Db\DbTarget(
    $db,
    '{{%yii_log}}',
    [LogLevel::ERROR, LogLevel::WARNING]
);
```

Alternatively, you can set levels after instantiation using the `setLevels()` method:

```php
$dbTarget = new \Yiisoft\Log\Target\Db\DbTarget($db);
$dbTarget->setLevels([LogLevel::ERROR, LogLevel::WARNING]);
```

You can use multiple databases to store log messages:

```php
/**
 * @var \Yiisoft\Db\Connection\ConnectionInterface $mysqlDb
 * @var \Yiisoft\Db\Connection\ConnectionInterface $sqliteDb
 */

$logger = new \Yiisoft\Log\Logger([
    new \Yiisoft\Log\Target\Db\DbTarget($mysqlDb),
    new \Yiisoft\Log\Target\Db\DbTarget($sqliteDb),
]);
```

## Documentation

For a description of using the logger, see the [yiisoft/log](https://github.com/yiisoft/log) package.

- [Yii guide to logging](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/logging.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Logging Library - DB Target is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
