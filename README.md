<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Logging Library - DB Target</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/log-target-db/v/stable.png)](https://packagist.org/packages/yiisoft/log-target-db)
[![Total Downloads](https://poser.pugx.org/yiisoft/log-target-db/downloads.png)](https://packagist.org/packages/yiisoft/log-target-db)
[![Build status](https://github.com/yiisoft/log-target-db/workflows/build/badge.svg)](https://github.com/yiisoft/log-target-db/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/log-target-db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/log-target-db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/log-target-db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/log-target-db/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Flog-target-db%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/log-target-db/master)
[![static analysis](https://github.com/yiisoft/log-target-db/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/log-target-db/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/log-target-db/coverage.svg)](https://shepherd.dev/github/yiisoft/log-target-db)

This package provides the Database target for the [yiisoft/log](https://github.com/yiisoft/log) library.

## Installation

The package could be installed with composer:

```
composer install yiisoft/log-target-db
```

## General usage

Creating a factory:

```php
$factory = new \Yiisoft\Log\Target\Db\DbFactory($container, $config);
```

- `$container (\Psr\Container\ContainerInterface)` - Container for getting an instance of a database connection.
- `$config (mixed)` - The configuration for creating a database connection instance.

The configuration can be specified in one of the following forms:

- A string: representing the class name of the object to be created.
- A configuration array: the array  must consist of `__class` contains name of the class to be instantiated,
  `__construct()` holds an array of constructor arguments. The rest of the config and property values and method calls.
  They are set/called in the order they appear in the array.
- A PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
  The callable should return a instance of the `\Yiisoft\Db\Connection\ConnectionInterface`.

For more information about container and configuration, see the description of the
[yiisoft/di](https://github.com/yiisoft/di) and [yiisoft/factory](https://github.com/yiisoft/factory) packages.

> This factory provides lazy loading of the `Yiisoft\Db\Connection\ConnectionInterface` instance
to prevent a circular reference to the connection when building container definitions.

Creating a target:

```php
$dbTarget = new \Yiisoft\Log\Target\Db\DbTarget($factory, $table);
```

- `$factory (\Yiisoft\Log\Target\Db\DbFactory)` - Factory for creating a database connection instance.
- `$table (string)` - The name of the database table to store the log messages. Defaults to "log".

Creating a logger:

```php
$logger = new \Yiisoft\Log\Logger([$dbTarget]);
```

You can use multiple databases to store log messages:

```php
/**
 * @var \Psr\Container\ContainerInterface $container
 * @var string $mysqlClass // Class name of the Mysql connection
 * @var array $redisConfig // Configuration array for creating a Redis connection
 */
 
$mysqlFactory = new \Yiisoft\Log\Target\Db\DbFactory($container, $mysqlClass);
$redisFactory = new \Yiisoft\Log\Target\Db\DbFactory($container, $redisConfig);

$logger = new \Yiisoft\Log\Logger([
    new \Yiisoft\Log\Target\Db\DbTarget($mysqlFactory),
    new \Yiisoft\Log\Target\Db\DbTarget($redisFactory),
]);
```

For a description of using the logger, see the [yiisoft/log](https://github.com/yiisoft/log) package.

For use in the [Yii framework](http://www.yiiframework.com/), see the configuration files:

- [`config/common.php`](https://github.com/yiisoft/log-target-db/blob/master/config/common.php)
- [`config/params.php`](https://github.com/yiisoft/log-target-db/blob/master/config/params.php)

You need to set up a database connection and run this console command to create tables to store the log messages:

```shell
./vendor/bin/yii migrate/up
```

See [Yii guide to logging](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/logging.md) for more info.

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Logging Library - Email Target is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
