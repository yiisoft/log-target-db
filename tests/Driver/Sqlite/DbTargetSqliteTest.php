<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Sqlite;

use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\SqliteHelper;

/**
 * @group sqlite
 */
final class DbTargetSqliteTest extends AbstractDbTargetTest
{
    protected function setUp(): void
    {
        $this->db = (new SqliteHelper())->createConnection();

        parent::setUp();
    }

    public function testExportWithStoreFailure(): void
    {
        if ($this->db->getTableSchema('log', true) !== null) {
            $this->db->createCommand('DROP TABLE log')->execute();
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such table: log');
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }
}
