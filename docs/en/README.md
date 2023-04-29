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

> Note: Additionally you can import the `RAW SQL` directly to create the tables.
>
>- [schema-mssql](/docs/en/migration/schema-mssql.sql).
>- [schema-mysql](/docs/en/migration/schema-mysql.sql).
>- [schema-oracle](/docs/en/migration/schema-oci.sql).
>- [schema-pgsql](/docs/en/migration/schema-pgsql.sql).
>- [schema-sqlite](/docs/en/migration/schema-sqlite.sql).

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
