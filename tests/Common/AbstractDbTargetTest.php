<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\DbTarget;

use function microtime;

abstract class AbstractDbTargetTest extends TestCase
{
    protected ConnectionInterface $db;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->db);
    }

    public function testGetters(): void
    {
        $target = $this->createDbTarget();

        $this->assertSame('log', $target->getTable());
        $this->assertSame($this->db, $target->getDb());
    }

    public function testExport(): void
    {
        $time = microtime(true);

        $this
            ->createDbTarget('{{%test-table-1}}')
            ->collect(
                [
                    new Message(LogLevel::INFO, 'Message', ['time' => $time, 'category' => 'application']),
                ],
                true,
            );

        $this
            ->createDbTarget('{{%test-table-2}}')
            ->collect(
                [
                    new Message(LogLevel::ALERT, 'Message-1', ['time' => $time, 'category' => 'app']),
                    new Message(LogLevel::ERROR, 'Message-2', ['time' => $time, 'foo' => 'bar']),
                ],
                true,
            );

        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::INFO,
                    'category' => 'application',
                    'log_time' => (string) $time,
                    'message' => '[info] Message',
                ],
            ],
            $this->findData('test-table-1'),
        );

        $this->assertEquals(
            [
                [
                    'id' => '1',
                    'level' => LogLevel::ALERT,
                    'category' => 'app',
                    'log_time' => (string) $time,
                    'message' => '[alert] Message-1',
                ],
                [
                    'id' => '2',
                    'level' => LogLevel::ERROR,
                    'category' => '',
                    'log_time' => (string) $time,
                    'message' => '[error] Message-2',
                ],
            ],
            $this->findData('test-table-2'),
        );
    }

    public function testExportWithEmptyMessages(): void
    {
        $this->createDbTarget('test-table-1')->collect([], true);
        $this->assertSame([], $this->findData('test-table-1'));
    }

    protected function createDbTarget(string $table = 'log'): DbTarget
    {
        $target = new DbTarget($this->db, $table);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");

        return $target;
    }

    private function findData(string $table): array
    {
        return (new Query($this->db))->from($table)->all();
    }
}
