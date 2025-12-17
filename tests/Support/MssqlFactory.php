<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mssql\Connection;
use Yiisoft\Db\Mssql\Driver;
use Yiisoft\Db\Mssql\Dsn;

final class MssqlFactory extends ConnectionFactory
{
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(
            new Dsn('sqlsrv', 'localhost', 'yiitest;TrustServerCertificate=1'),
            'SA',
            'YourStrong!Passw0rd',
        );

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
