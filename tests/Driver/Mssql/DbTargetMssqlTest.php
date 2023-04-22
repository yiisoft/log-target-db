<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Mssql;

use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 */
final class DbTargetMssqlTest extends AbstractDbTargetTest
{
    protected function setUp(): void
    {
        $this->db = (new MssqlHelper())->createConnection();

        parent::setUp();
    }

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
