<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Query\Query;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Log\Target\Db\DbSchemaManager;

abstract class AbstractDbTargetTest extends TestCase
{
    protected ConnectionInterface $db;
    protected DbSchemaManager $dbSchemaManager;
    protected string $time = '2023-04-23 12:34:56.123456';

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    protected function setup(): void
    {
        // create db manager
        $this->dbSchemaManager = new DbSchemaManager($this->db);

        // create migration tables
        $this->dbSchemaManager->ensureTable('{{%test-table-1}}');
        $this->dbSchemaManager->ensureTable('{{%test-table-2}}');

        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        // drop tables
        $this->dbSchemaManager->ensureNoTable('{{%test-table-1}}');
        $this->dbSchemaManager->ensureNoTable('{{%test-table-2}}');

        $this->db->close();

        unset($this->db);

        parent::tearDown();
    }

    public function testGetters(): void
    {
        $target = $this->createDbTarget();

        $this->assertSame('log', $target->getTable());
        $this->assertSame($this->db, $target->getDb());
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function testExport(): void
    {
        $this
            ->createDbTarget('{{%test-table-1}}')
            ->collect(
                [
                    new Message(LogLevel::INFO, 'Message', ['time' => $this->time, 'category' => 'application']),
                ],
                true,
            );

        $this
            ->createDbTarget('{{%test-table-2}}')
            ->collect(
                [
                    new Message(LogLevel::ALERT, 'Message-1', ['time' => $this->time, 'category' => 'app']),
                    new Message(LogLevel::ERROR, 'Message-2', ['time' => $this->time, 'foo' => 'bar']),
                ],
                true,
            );

        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::INFO,
                    'category' => 'application',
                    'log_time' => $this->time,
                    'message' => '[info] Message',
                ],
            ],
            $this->findData('{{%test-table-1}}'),
        );

        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::ALERT,
                    'category' => 'app',
                    'log_time' => $this->time,
                    'message' => '[alert] Message-1',
                ],
                [
                    'id' => '2',
                    'level' => LogLevel::ERROR,
                    'category' => '',
                    'log_time' => $this->time,
                    'message' => '[error] Message-2',
                ],
            ],
            $this->findData('{{%test-table-2}}'),
        );
    }

    public function testExportWithoutLogTime(): void
    {
        $this->createDbTarget('{{%test-table-1}}')->collect([new Message(LogLevel::INFO, 'Message')], true);

        $data = $this->findData('{{%test-table-1}}');

        $this->assertInstanceOf(DateTime::class, DateTime::createFromFormat('Y-m-d H:i:s.u', $data[0]['log_time']));
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
            $data,
        );
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function testExportWithEmptyMessages(): void
    {
        $this->createDbTarget('{{%test-table-1}}')->collect([], true);
        $this->assertSame([], $this->findData('{{%test-table-1}}'));
    }

    public function testSetLevelsViaConstructor(): void
    {
        $target = new DbTarget($this->db, '{{%test-table-1}}', [LogLevel::ERROR, LogLevel::INFO]);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");

        $target->collect(
            [
                new Message(LogLevel::INFO, 'message-1', ['time' => $this->time]),
                new Message(LogLevel::DEBUG, 'message-2', ['time' => $this->time]),
                new Message(LogLevel::ERROR, 'message-3', ['time' => $this->time]),
            ],
            true
        );

        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::INFO,
                    'category' => '',
                    'log_time' => $this->time,
                    'message' => '[info] message-1',
                ],
                [
                    'id' => '2',
                    'level' => LogLevel::ERROR,
                    'category' => '',
                    'log_time' => $this->time,
                    'message' => '[error] message-3',
                ],
            ],
            $this->findData('{{%test-table-1}}'),
        );
    }

    protected function createDbTarget(string $table = 'log'): DbTarget
    {
        $target = new DbTarget($this->db, $table);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");

        return $target;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function findData(string $table): array
    {
        return (new Query($this->db))->from($table)->all();
    }
}
