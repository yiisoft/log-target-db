<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Pgsql;

use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\PgsqlHelper;

/**
 * @group pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbTargetPgsqlTest extends AbstractDbTargetTest
{
    protected function setUp(): void
    {
        $this->db = (new PgsqlHelper())->createConnection();

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
        $this->expectExceptionMessage('SQLSTATE[42P01]: Undefined table: 7 ERROR:  relation "log" does not exist');
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }
}
