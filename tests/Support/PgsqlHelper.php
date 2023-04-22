<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;

final class PgsqlHelper extends ConnectionHelper
{
    private string $dsn = 'pgsql:host=127.0.0.1;dbname=yiitest;port=5432';
    private string $username = 'root';
    private string $password = 'root';
    private string $charset = 'UTF8';

    public function createConnection(
        bool $reset = true,
        string $fixture = __DIR__ . '/Fixture/schema-pgsql.sql'
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
