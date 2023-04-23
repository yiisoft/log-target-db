<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Pgsql\Connection;
use Yiisoft\Db\Pgsql\Driver;

final class PgsqlHelper extends ConnectionHelper
{
    private string $dsn = 'pgsql:host=127.0.0.1;dbname=yiitest;port=5432';
    private string $username = 'root';
    private string $password = 'root';
    private string $charset = 'UTF8';

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function createConnection(bool $reset = true): ConnectionInterface
    {
        $pdoDriver = new Driver($this->dsn, $this->username, $this->password);
        $pdoDriver->charset($this->charset);

        $db = new Connection($pdoDriver, $this->createSchemaCache());

        if ($reset) {
            DbHelper::loadFixture(
                $db,
                __DIR__ . '/Fixture/schema-pgsql.sql',
                dirname(__DIR__, 2) . '/src/Migration/schema-pgsql.sql',
            );
        }

        return $db;
    }
}
