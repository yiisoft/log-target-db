<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

final class SqliteFactory extends ConnectionFactory
{
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(new Dsn('sqlite', __DIR__ . '/runtime/yiitest.sq3'));

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
