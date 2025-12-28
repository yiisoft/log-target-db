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
use Yiisoft\Log\Target\Db\DbTarget;

use function array_splice;
use function implode;
use function strcmp;
use function usort;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractSQLDumpFileTest extends TestCase
{
    protected ConnectionInterface $db;
    protected string $idType = ColumnType::BIGINT;
    protected string $logTime = ColumnType::TIMESTAMP;
    protected string $messageType = ColumnType::TEXT;
    private string $driverName = '';
    private string $table = '{{%yii_log}}';

    protected function setup(): void
    {
        parent::setUp();

        $this->driverName = $this->db->getDriverName();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->db, $this->driverName, $this->idType, $this->logTime, $this->messageType);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableAndDropTable(): void
    {
        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $this->assertNotNull($this->db->getTableSchema($this->table, true));

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($this->table, true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(): void
    {
        $dbTarget = new DbTarget($this->db);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

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
            $this->assertSame($expectedIndex[$key], $index->columnNames[0], 'columns');
            $this->assertSame($expectedIndexName[$key], $index->name, 'name');
            $this->assertSame($expectedIsPrimary[$key], $index->isPrimaryKey, 'primary');
            $this->assertSame($expectedIsUnique[$key], $index->isUnique, 'unique');
        }

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($dbTarget->getTable(), true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructure(): void
    {
        $dbTarget = new DbTarget($this->db);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

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

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($dbTarget->getTable(), true));
    }

    /**
     * Loads the fixture into the database.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    private function loadFromSQLDumpFile(string $fixture): void
    {
        $this->db->open();

        if ($this->driverName === 'oci') {
            [$creates] = explode('/* STATEMENTS */', file_get_contents($fixture), 1);
            if (!str_contains($creates, '/* TRIGGERS */')) {
                $lines = explode(';', $creates);
            } else {
                [$statements, $triggers] = explode('/* TRIGGERS */', $creates, 2);
                $lines = array_merge(
                    explode(';', $statements),
                    explode('/', $triggers),
                );
            }
        } else {
            $lines = explode(';', file_get_contents($fixture));
        }

        foreach ($lines as $line) {
            $this->db->createCommand(trim($line))->execute();
        }
    }
}
