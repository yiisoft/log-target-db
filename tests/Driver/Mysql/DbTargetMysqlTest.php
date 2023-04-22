<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Mysql;

use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 */
final class DbTargetMysqlTest extends AbstractDbTargetTest
{
    protected function setUp(): void
    {
        $this->db = (new MysqlHelper())->createConnection();

        parent::setUp();
    }

    public function testExportWithStoreFailure(): void
    {
        if ($this->db->getTableSchema('log', true) !== null) {
            $this->db->createCommand('DROP TABLE log')->execute();
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'yiitest.log' doesn't exist"
        );
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }
}
