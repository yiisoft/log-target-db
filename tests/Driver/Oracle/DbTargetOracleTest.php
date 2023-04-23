<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbTargetTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleHelper;

/**
 * @group oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbTargetOracleTest extends AbstractDbTargetTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        $this->db = (new OracleHelper())->createConnection();

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
            'SQLSTATE[HY000]: General error: 942 OCIStmtExecute: ORA-00942: table or view does not exist'
        );
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }
}
