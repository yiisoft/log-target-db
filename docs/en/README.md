# Getting started

## Requirements

- The minimum version of PHP required by this package is `8.0`.
- `PDO` PHP extension.

## Installation

The package could be installed with composer:

```
composer require yiisoft/log-target-db --prefer-dist
```

## Migration

The package provides a migration that creates the cache table for default `{{%log}}`. You can use it as follows:

```php
Migration::ensureTable($db);
```

For custom table name you can use:

```php
Migration::ensureTable($db, '{{%custom_log_table}}');
```

For dropping table you can use:

```php
Migration::dropTable($db);
```

For custom table name you can use:

```php
Migration::dropTable($db, '{{%custom_log_table}}');
```

## General usage

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

You need to set up a database connection and run this console command to create tables to store the log messages:

```shell
./yii migrate/up
```

See [Yii guide to logging](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/logging.md) for more info.
