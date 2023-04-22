<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Mssql\Connection;
use Yiisoft\Db\Mssql\Driver;

final class MssqlHelper extends ConnectionHelper
{
    private string $dsn = 'sqlsrv:Server=127.0.0.1,1433;Database=yiitest';
    private string $username = 'SA';
    private string $password = 'YourStrong!Passw0rd';
    private string $charset = 'UTF8MB4';

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createConnection(
        bool $reset = true,
        string $fixture = __DIR__ . '/Fixture/schema-mssql.sql'
    ): ConnectionInterface {
        $pdoDriver = new Driver($this->dsn, $this->username, $this->password);
        $pdoDriver->charset($this->charset);

        $db = new Connection($pdoDriver, $this->createSchemaCache());

        if ($reset) {
            DbHelper::loadFixture($db, $fixture);
        }

        return $db;
    }
}
