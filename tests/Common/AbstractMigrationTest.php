<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbHelper;
use Yiisoft\Log\Target\Db\DbTarget;

abstract class AbstractMigrationTest extends TestCase
{
    protected ConnectionInterface $db;
    private string $idType = '';
    private Logger $logger;
    private string $logTime = '';
    private string $messageType = '';

    protected function setUp(): void
    {
        DbHelper::ensureTable($this->db);
        DbHelper::ensureTable($this->db, '{{%test-table-1}}');
        DbHelper::ensureTable($this->db, '{{%test-table-2}}');

        $this->idType = match ($this->db->getDriverName()) {
            'oci', 'sqlite' => 'integer',
            default => 'bigint'
        };

        $this->logTime = match ($this->db->getDriverName()) {
            'sqlsrv' => 'datetime',
            default => 'timestamp',
        };

        $this->logger = new Logger(
            [
                new DbTarget($this->db, '{{%test-table-1}}'),
                new DbTarget($this->db, '{{%test-table-2}}'),
            ],
        );

        $this->messageType = match ($this->db->getDriverName()) {
            'sqlsrv' => 'string',
            default => 'text',
        };

        parent::setup();
    }

    protected function tearDown(): void
    {
        DbHelper::dropTable($this->db);
        DbHelper::dropTable($this->db, '{{%test-table-1}}');
        DbHelper::dropTable($this->db, '{{%test-table-2}}');

        $this->db->close();

        unset($this->db, $this->idType, $this->logger);

        parent::tearDown();
    }

    public static function tableListProvider(): array
    {
        return [
            ['test-table-1'],
            ['test-table-2'],
        ];
    }

    /**
     * @dataProvider tableListProvider
     */
    public function testVerifyTableStructure(string $table): void
    {
        $tableSchema = $this->db->getTableSchema($table);

        $this->assertSame($table, $tableSchema?->getName());
        $this->assertSame(['id'], $tableSchema?->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema?->getColumnNames());
        $this->assertSame($this->idType, $tableSchema?->getColumn('id')->getType());
        $this->assertSame('string', $tableSchema?->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema?->getColumn('level')->getSize());
        $this->assertSame('string', $tableSchema?->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema?->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema?->getColumn('log_time')->getType());
        $this->assertSame($this->messageType, $tableSchema?->getColumn('message')->getType());
    }

    public function testVerifyTableLogStructure(): void
    {
        $tableSchema = $this->db->getTableSchema('{{%log}}');

        $this->assertSame('log', $tableSchema?->getName());
        $this->assertSame(['id'], $tableSchema?->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema?->getColumnNames());
        $this->assertSame($this->idType, $tableSchema?->getColumn('id')->getType());
        $this->assertSame('string', $tableSchema?->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema?->getColumn('level')->getSize());
        $this->assertSame('string', $tableSchema?->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema?->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema?->getColumn('log_time')->getType());
        $this->assertSame($this->messageType, $tableSchema?->getColumn('message')->getType());
    }
}
