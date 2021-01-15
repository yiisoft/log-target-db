<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbFactory;
use Yiisoft\Log\Target\Db\DbTarget;

return [
    LoggerInterface::class => static fn (ContainerInterface $container) => new Logger([
        new DbTarget(new DbFactory($container, ConnectionInterface::class)),
    ]),
];
