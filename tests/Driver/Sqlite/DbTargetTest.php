<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Sqlite;

use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\SqliteHelper;

/**
 * @group sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbTargetTest extends AbstractDbTargetTest
{
    protected function setUp(): void
    {
        $this->db = (new SqliteHelper())->createConnection();

        $this->db->setTablePrefix('sqlite3_');

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
            $this->db->createCommand('DROP TABLE log')->execute();
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such table: log');
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('sqlite3_log', $this->db->getSchema()->getRawTableName('{{%log}}'));
        $this->assertSame('sqlite3_test-table-1', $this->db->getSchema()->getRawTableName('{{%test-table-1}}'));
        $this->assertSame('sqlite3_test-table-2', $this->db->getSchema()->getRawTableName('{{%test-table-2}}'));
    }
}
