<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Query\Query;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Log\Target\Db\Migration\M202101052207CreateLog;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;

use function microtime;

final class DbTargetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $migration = new M202101052207CreateLog(
            $this->getContainer()->get(LoggerInterface::class),
            $this->getContainer()->get(MigrationInformerInterface::class),
        );
        $migration->up($this->getContainer()->get(MigrationBuilder::class));
    }

    public function testGetters(): void
    {
        $target = $this->createDbTarget();

        $this->assertSame('log', $target->getTable());
        $this->assertSame($this->getContainer()->get(ConnectionInterface::class), $target->getDb());
    }

    public function testExport(): void
    {
        $time = microtime(true);

        $this
            ->createDbTarget(null, 'test-table-1')
            ->collect(
                [
                    new Message(LogLevel::INFO, 'Message', ['time' => $time, 'category' => 'application']),
                ],
                true,
            );

        $this
            ->createDbTarget(null, 'test-table-2')
            ->collect(
                [
                    new Message(LogLevel::ALERT, 'Message-1', ['time' => $time, 'category' => 'app']),
                    new Message(LogLevel::ERROR, 'Message-2', ['time' => $time, 'foo' => 'bar']),
                ],
                true,
            );

        $this->assertSame(
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

        $this->assertSame(
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
        $this->createDbTarget(null, 'test-table-1')->collect([], true);
        $this->assertSame([], $this->findData('test-table-1'));
    }

    public function testExportWithStoreFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such table: log');
        $this->createDbTarget()->collect([new Message(LogLevel::INFO, 'Message')], true);
    }

    private function createDbTarget(ConnectionInterface $db = null, string $table = 'log'): DbTarget
    {
        $target = new DbTarget($db ?? $this->getContainer()->get(ConnectionInterface::class), $table);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");

        return $target;
    }

    private function findData(string $table): array
    {
        return (new Query($this->getContainer()->get(ConnectionInterface::class)))->from($table)->all();
    }
}
