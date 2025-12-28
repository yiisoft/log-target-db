<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constant\ColumnType;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Log\Target\Db\DbSchemaManager;
use Yiisoft\Log\Target\Db\DbTarget;

use function array_splice;
use function implode;
use function strcmp;
use function usort;

abstract class AbstractDbSchemaManagerTest extends TestCase
{
    protected ConnectionInterface $db;
    protected string $idType = ColumnType::BIGINT;
    protected string $logTime = ColumnType::TIMESTAMP;
    protected string $messageType = ColumnType::TEXT;
    private DbSchemaManager $dbSchemaManager;

    protected function setup(): void
    {
        parent::setUp();

        $this->dbSchemaManager = new DbSchemaManager($this->db);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->db, $this->dbSchemaManager, $this->idType, $this->logTime, $this->messageType);
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableAndEnsureNoTable(string $table): void
    {
        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureNoTable($table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableExist(string $table): void
    {
        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureNoTable($table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(string $table): void
    {
        $dbTarget = new DbTarget($this->db, $table);

        $this->dbSchemaManager->ensureTable($dbTarget->getTable());

        $tableRawName = $this->db->getQuoter()->getRawTableName($dbTarget->getTable());
        $expectedIndex = ['category', 'id', 'level', 'log_time'];
        $expectedIndexName = [
            "IDX_$tableRawName-category",
            "PK_$tableRawName",
            "IDX_$tableRawName-level",
            "IDX_$tableRawName-time",
        ];
        $expectedIsPrimary = [false, true, false, false];
        $expectedIsUnique = [false, true, false, false];

        if ($this->db->getDriverName() === 'mysql') {
            $expectedIndexName = [
                "IDX_$tableRawName-category",
                'PRIMARY',
                "IDX_$tableRawName-level",
                "IDX_$tableRawName-time",
            ];
        }

        if ($this->db->getDriverName() === 'sqlite') {
            $expectedIndexName = ["IDX_$tableRawName-category", '', "IDX_$tableRawName-level", "IDX_$tableRawName-time"];
        }

        $schema = $this->db->getSchema();

        $indexes = $schema->getTableIndexes($dbTarget->getTable(), true);

        usort(
            $indexes,
            static fn ($a, $b) => strcmp(
                implode('', $a->columnNames),
                implode('', $b->columnNames),
            )
        );

        if ($this->db->getDriverName() === 'oci') {
            array_splice($indexes, 0, 1);
        }

        foreach ($indexes as $key => $index) {
            $this->assertSame($expectedIndex[$key], $index->columnNames[0]);
            $this->assertSame($expectedIndexName[$key], $index->name);
            $this->assertSame($expectedIsPrimary[$key], $index->isPrimaryKey);
            $this->assertSame($expectedIsUnique[$key], $index->isUnique);
        }

        $this->dbSchemaManager->ensureNoTable($dbTarget->getTable());

        $this->assertNull($this->db->getTableSchema($dbTarget->getTable(), true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructure(string $table): void
    {
        $dbTarget = new DbTarget($this->db, $table);

        $this->dbSchemaManager->ensureTable($dbTarget->getTable());

        $tableSchema = $this->db->getTableSchema($dbTarget->getTable());
        $tableRawName = $this->db->getQuoter()->getRawTableName($dbTarget->getTable());

        $this->assertSame($tableRawName, $tableSchema?->getName());
        $this->assertSame(['id'], $tableSchema?->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema?->getColumnNames());
        $this->assertSame($this->idType, $tableSchema?->getColumn('id')->getType());
        $this->assertSame(ColumnType::STRING, $tableSchema?->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema?->getColumn('level')->getSize());
        $this->assertSame(ColumnType::STRING, $tableSchema?->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema?->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema?->getColumn('log_time')->getType());
        $this->assertSame($this->messageType, $tableSchema?->getColumn('message')->getType());

        $this->dbSchemaManager->ensureNoTable($dbTarget->getTable());

        $this->assertNull($this->db->getTableSchema($dbTarget->getTable(), true));
    }

    public static function tableNameProvider(): array
    {
        return [
            ['{{%yii_log}}'],
            ['{{%custom_yii_log}}'],
        ];
    }
}
