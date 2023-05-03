<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
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

|                      Packages                       |      PHP      |    Versions     |                                                                        CI-Actions                                                                         |
|:---------------------------------------------------:|:-------------:|:---------------:|:---------------------------------------------------------------------------------------------------------------------------------------------------------:|
|  [[db-mssql]](https://github.com/yiisoft/db-mssql)  | **8.0 - 8.2** | **2017 - 2022** |  [![mssql](https://github.com/yiisoft/log-target-db/actions/workflows/mssql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/mssql.yml)   | |
|  [[db-mysql/mariadb]](https://github.com/yiisoft/db-mysql)  | **8.0 - 8.2** |  **5.7-8.0**/**10.4-10.10**  |  [![mysql](https://github.com/yiisoft/log-target-db/actions/workflows/mysql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/mysql.yml)   |
| [[db-oracle]](https://github.com/yiisoft/db-oracle) | **8.0 - 8.2** |  **11C - 21C**  | [![oracle](https://github.com/yiisoft/log-target-db/actions/workflows/oracle.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/oracle.yml) |
|  [[db-pgsql]](https://github.com/yiisoft/db-pgsql)  | **8.0 - 8.2** | **9.0 - 15.0**  |  [![pgsql](https://github.com/yiisoft/log-target-db/actions/workflows/pgsql.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/pgsql.yml)   |
| [[db-sqlite]](https://github.com/yiisoft/db-sqlite) | **8.0 - 8.2** |  **3:latest**   | [![sqlite](https://github.com/yiisoft/log-target-db/actions/workflows/sqlite.yml/badge.svg)](https://github.com/yiisoft/log-target-db/actions/workflows/sqlite.yml) |


## Requirements

- PHP 8.0 or higher.
- `PDO` PHP extension.

## Installation

The package could be installed with composer:

```
composer require yiisoft/log-target-db --prefer-dist
```

## Database Preparing

Package provides two way for preparing database:

1. Raw SQL. You can use it with the migration package used in your application.

    - [MSSQL](/docs/en/migration/schema-mssql.sql),
    - [MySQL / MariaDB](/docs/en/migration/schema-mysql.sql),
    - [Oracle](/docs/en/migration/schema-oci.sql),
    - [PostgreSQL](/docs/en/migration/schema-pgsql.sql),
    - [SQLite](/docs/en/migration/schema-sqlite.sql),

2. `DbHelper` for create/drop cache table (by default `{{%log}}`).

```php
// Create table with default name
DbHelper::ensureTable($db);

// Create table with custom name
DbHelper::ensureTable($db, '{{%custom_log}}');

// Drop table with default name
DbHelper::dropTable($db);

// Drop table with custom name
DbHelper::dropTable($db, '{{%custom_log}}');
```

## General usage

When creating an instance of `\Yiisoft\Log\Logger`, you must pass an instance of the database connection,
for more information see [yiisoft/db](https://github.com/yiisoft/db/tree/master/docs/en#create-connection).

Creating a target:

```php
$dbTarget = new \Yiisoft\Log\Target\Db\DbTarget($db, $table);
```

- `$db (\Yiisoft\Db\Connection\ConnectionInterface)` - The database connection instance.
- `$table (string)` - The name of the database table to store the log messages. Defaults to "log".

Creating a logger:

```php
$logger = new \Yiisoft\Log\Logger([$dbTarget]);
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

For a description of using the logger, see the [yiisoft/log](https://github.com/yiisoft/log) package.

For use in the [Yii framework](http://www.yiiframework.com/), see the configuration files:

- [`config/common.php`](https://github.com/yiisoft/log-target-db/blob/master/config/common.php)
- [`config/params.php`](https://github.com/yiisoft/log-target-db/blob/master/config/params.php)

See [Yii guide to logging](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/logging.md) for more info.


## Support

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/db/68) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## Testing

[Check the testing instructions](/docs/en/testing.md) to learn about testing.

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Logging Library - DB Target is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
