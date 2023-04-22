<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

final class SqliteHelper extends ConnectionHelper
{
    private string $dsn = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    private string $charset = 'UTF8MB4';

    public function createConnection(
        bool $reset = true,
        string $fixture = __DIR__ . '/Fixture/schema-sqlite.sql'
    ): ConnectionInterface {
        $pdoDriver = new Driver($this->dsn, '', '');
        $pdoDriver->charset($this->charset);

        $db = new Connection($pdoDriver, $this->createSchemaCache());

        if ($reset) {
            DbHelper::loadFixture($db, $fixture);
        }

        return $db;
    }
}
