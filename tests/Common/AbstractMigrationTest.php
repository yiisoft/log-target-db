<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbTarget;

abstract class AbstractMigrationTest extends TestCase
{
    protected ConnectionInterface $db;
    protected ConnectionInterface $dbFixture;
    protected string $idType = '';
    protected Logger $logger;
    protected string $logTime = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->idType = match ($this->db->getDriverName()) {
            'mysql' => 'bigint',
            'oracle' => 'number',
            'pgsql' => 'bigint',
            'sqlsrv' => 'bigint',
            default => 'integer'
        };

        $this->logTime = match ($this->db->getDriverName()) {
            'sqlsrv' => 'decimal',
            default => 'double'
        };

        $this->logger = new Logger(
            [
                new DbTarget($this->db, 'test-table-1'),
                new DbTarget($this->db, 'test-table-2'),
            ],
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->db, $this->dbFixture, $this->idType, $this->logger);
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

        $this->assertSame($table, $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema->getColumnNames());
        $this->assertSame($this->idType, $tableSchema->getColumn('id')->getType());
        $this->assertSame('string', $tableSchema->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema->getColumn('level')->getSize());
        $this->assertSame('string', $tableSchema->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema->getColumn('log_time')->getType());
        $this->assertSame('text', $tableSchema->getColumn('message')->getType());
    }

    public function testVerifyTableLogStructure(): void
    {
        $table = 'log';
        $tableSchema = $this->dbFixture->getTableSchema($table);

        $this->assertSame($table, $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema->getColumnNames());
        $this->assertSame($this->idType, $tableSchema->getColumn('id')->getType());
        $this->assertSame('string', $tableSchema->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema->getColumn('level')->getSize());
        $this->assertSame('string', $tableSchema->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema->getColumn('log_time')->getType());
        $this->assertSame('text', $tableSchema->getColumn('message')->getType());
    }
}
