<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Mssql;

use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\MssqlFactory;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbTargetTest extends AbstractDbTargetTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MssqlFactory())->createConnection();

        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function testExportWithStoreFailure(): void
    {
        if ($this->db->getTableSchema('log', true) !== null) {
            $this->db->createCommand('DROP TABLE {{log}}')->execute();
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "SQLSTATE[42S02]: [Microsoft][ODBC Driver 17 for SQL Server][SQL Server]Invalid object name 'log'"
        );
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }
}
