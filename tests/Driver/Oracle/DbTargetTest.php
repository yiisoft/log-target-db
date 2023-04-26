<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use DateTime;
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
final class DbTargetTest extends AbstractDbTargetTest
{
    protected string $time = '23-APR-23 12.34.56.123456 PM';

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
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
        if ($this->db->getTableSchema('{{%log}}', true) !== null) {
            $this->db->createCommand('DROP TABLE {{%log}}')->execute();
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'SQLSTATE[HY000]: General error: 942 OCIStmtExecute: ORA-00942: table or view does not exist'
        );
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }

    public function testExportWithoutLogTime(): void
    {
        $this->createDbTarget('{{%test-table-1}}')->collect([new Message(LogLevel::INFO, 'Message')], true);

        $data = $this->findData('{{%test-table-1}}');

        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat('y-M-d H.i.s.u A', $data[0]['log_time']));
        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::INFO,
                    'category' => '',
                    'log_time' => $data[0]['log_time'],
                    'message' => '[info] Message',
                ],
            ],
            $this->findData('{{%test-table-1}}'),
        );
    }
}
