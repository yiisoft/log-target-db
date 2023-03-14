<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\StreamTarget;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Log\Target\Db\Migration\M202101052207CreateLog;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;

final class MigrationTest extends TestCase
{
    private MigrationInformerInterface $migrationInformer;

    public function setUp(): void
    {
        parent::setUp();

        $this->migrationInformer = $this
            ->getContainer()
            ->get(MigrationInformerInterface::class);
    }

    public function testUpAndDown(): void
    {
        $migration = new M202101052207CreateLog(
            $this
                ->getContainer()
                ->get(LoggerInterface::class),
            $this->migrationInformer,
        );

        $migration->up($this
            ->getContainer()
            ->get(MigrationBuilder::class));

        $this->assertTrue($this->tableExists('test-table-1'));
        $this->assertTrue($this->tableExists('test-table-2'));
        $this->assertFalse($this->tableExists('table-not-exist'));

        $migration->down($this
            ->getContainer()
            ->get(MigrationBuilder::class));

        $this->assertFalse($this->tableExists('test-table-1'));
        $this->assertFalse($this->tableExists('test-table-2'));
        $this->assertFalse($this->tableExists('table-not-exist'));
    }

    public function testUpWithCheckingEquivalenceOfConnectionInstance(): void
    {
        /** @var Logger $logger */
        $logger = $this
            ->getContainer()
            ->get(LoggerInterface::class);

        $migration = new M202101052207CreateLog($logger, $this->migrationInformer);
        $migration->up($this
            ->getContainer()
            ->get(MigrationBuilder::class));

        /** @var DbTarget[] $targets */
        $targets = $logger->getTargets();

        $this->assertSame($targets[0]->getDb(), $targets[1]->getDb());
    }

    public function testConstructorThrowExceptionForNotExistentDbTargetOfLogger(): void
    {
        $this->expectException(RuntimeException::class);
        new M202101052207CreateLog(new Logger([new StreamTarget()]), $this->migrationInformer);
    }

    public function testConstructorThrowExceptionForNotYiiLogger(): void
    {
        $this->expectException(RuntimeException::class);
        new M202101052207CreateLog(new class () extends AbstractLogger implements LoggerInterface {
            public function log($level, $message, array $context = []): void
            {
            }
        }, $this->migrationInformer);
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this
            ->getContainer()
            ->get(ConnectionInterface::class)
            ->createCommand("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='{$table}'")
            ->queryScalar()
        ;
    }
}
