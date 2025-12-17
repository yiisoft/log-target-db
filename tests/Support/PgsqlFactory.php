<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;
use Yiisoft\Db\Pgsql\Dsn;

final class PgsqlFactory extends ConnectionFactory
{
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(
            new Dsn('pgsql', '127.0.0.1', 'yiitest', '5432'),
            'root',
            'root',
        );

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
