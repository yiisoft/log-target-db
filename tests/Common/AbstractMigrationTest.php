<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Log\Target\Db\Migration;

abstract class AbstractMigrationTest extends TestCase
{
    protected ConnectionInterface $db;
    private string $idType = '';
    private Logger $logger;
    private string $logTime = '';
    private string $messageType = '';

    protected function setUp(): void
    {
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
        // drop tables
        Migration::dropTable($this->db);
        Migration::dropTable($this->db, '{{%test-table-1}}');
        Migration::dropTable($this->db, '{{%test-table-2}}');

        $this->db->close();

        unset($this->db, $this->idType, $this->logger);

        parent::tearDown();
    }

    public function testDropTable(): void
    {
        Migration::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    public function testEnsureTable(): void
    {
        $table = '{{%log}}';

        Migration::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTableExist(): void
    {
        $table = '{{%log}}';

        Migration::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table));

        Migration::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table));
    }

    public static function tableListProviderWithPrefix(): array
    {
        return [
            ['{{%test-table-1}}', 'test-table-1'],
            ['{{%test-table-2}}', 'test-table-2'],
        ];
    }

    public static function tableListIndexesProvider(): array
    {
        return [
            ['{{%log}}', 'log'],
            ['{{%test-table-1}}', 'test-table-1'],
            ['{{%test-table-2}}', 'test-table-2'],
        ];
    }

    /**
     * @dataProvider tableListProviderWithPrefix
     */
    public function testVerifyTableStructure(string $tableWithPrefix, string $table): void
    {
        Migration::ensureTable($this->db, $tableWithPrefix);

        $tableSchema = $this->db->getTableSchema($tableWithPrefix);
        $prefix = $this->db->getTablePrefix();

        $this->assertSame($prefix . $table, $tableSchema?->getName());
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
        Migration::ensureTable($this->db);

        $tableSchema = $this->db->getTableSchema('{{%log}}');
        $prefix = $this->db->getTablePrefix();

        $this->assertSame($prefix . 'log', $tableSchema?->getName());
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
