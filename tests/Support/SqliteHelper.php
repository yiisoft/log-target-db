<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

final class SqliteHelper extends ConnectionHelper
{
    private string $dsn = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    private string $charset = 'UTF8MB4';

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function createConnection(bool $reset = true): ConnectionInterface
    {
        $pdoDriver = new Driver($this->dsn, '', '');
        $pdoDriver->charset($this->charset);

        $db = new Connection($pdoDriver, $this->createSchemaCache());

        if ($reset) {
            DbHelper::loadFixture(
                $db,
                __DIR__ . '/Fixture/schema-sqlite.sql',
                dirname(__DIR__, 2) . '/src/Migration/schema-sqlite.sql',
            );
        }

        return $db;
    }
}
