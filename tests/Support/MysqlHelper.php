<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;

final class MysqlHelper extends ConnectionHelper
{
    private string $dsn = 'mysql:host=127.0.0.1;dbname=yiitest;port=3306';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'UTF8MB4';

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createConnection(
        bool $reset = true,
        string $fixture = __DIR__ . '/Fixture/schema-mysql.sql'
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
