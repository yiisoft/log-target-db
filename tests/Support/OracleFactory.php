<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use PDO;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Oracle\Connection;
use Yiisoft\Db\Oracle\Driver;
use Yiisoft\Db\Oracle\Dsn;

final class OracleFactory extends ConnectionFactory
{
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(
            new Dsn('oci', 'localhost', 'XE', '1521', ['charset' => 'AL32UTF8']),
            'system',
            'root'
        );
        $pdoDriver->attributes([PDO::ATTR_STRINGIFY_FETCHES => true]);

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
