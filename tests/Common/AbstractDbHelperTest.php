<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Log\Target\Db\DbHelper;

abstract class AbstractDbHelperTest extends TestCase
{
    protected ConnectionInterface $db;
    protected string $idType = SchemaInterface::TYPE_BIGINT;
    protected string $logTime = SchemaInterface::TYPE_TIMESTAMP;
    protected string $messageType = SchemaInterface::TYPE_TEXT;

    protected function tearDown(): void
    {
        $this->db->close();

        unset($this->db, $this->idType);

        parent::tearDown();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function testDropTable(): void
    {
        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema('{{%log}}', true));

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function testDropTableWithCustomTableName(): void
    {
        DbHelper::ensureTable($this->db, '{{%custom-log}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom-log}}', true));

        DbHelper::dropTable($this->db, '{{%custom-log}}');

        $this->assertNull($this->db->getTableSchema('{{%custom-log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTable(): void
    {
        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema('{{%log}}', true));

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableWithCustomTableName(): void
    {
        DbHelper::ensureTable($this->db, '{{%custom-log}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom-log}}', true));

        DbHelper::dropTable($this->db, '{{%custom-log}}');

        $this->assertNull($this->db->getTableSchema('{{%custom-log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableExist(): void
    {
        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema('{{%log}}'));

        DbHelper::ensureTable($this->db, '{{%log}}');

        $this->assertNotNull($this->db->getTableSchema('{{%log}}'));

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableExistWithCustomTableName(): void
    {
        DbHelper::ensureTable($this->db, '{{%custom-log}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom-log}}'));

        DbHelper::ensureTable($this->db, '{{%custom-log}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom-log}}'));

        DbHelper::dropTable($this->db, '{{%custom-log}}');

        $this->assertNull($this->db->getTableSchema('{{%custom-log}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructure(): void
    {
        DbHelper::ensureTable($this->db);

        $tableSchema = $this->db->getTableSchema('{{%log}}');
        $prefix = $this->db->getTablePrefix();

        $this->assertSame($prefix . 'log', $tableSchema?->getName());
        $this->assertSame(['id'], $tableSchema?->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema?->getColumnNames());
        $this->assertSame($this->idType, $tableSchema?->getColumn('id')->getType());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema?->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema?->getColumn('level')->getSize());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema?->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema?->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema?->getColumn('log_time')->getType());
        $this->assertSame($this->messageType, $tableSchema?->getColumn('message')->getType());

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    /**
     * @dataProvider tableListWithPrefixProvider
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructureWithCustomTableName(string $tableWithPrefix, string $table): void
    {
        DbHelper::ensureTable($this->db, $tableWithPrefix);

        $tableSchema = $this->db->getTableSchema($tableWithPrefix);
        $prefix = $this->db->getTablePrefix();

        $this->assertSame($prefix . $table, $tableSchema?->getName());
        $this->assertSame(['id'], $tableSchema?->getPrimaryKey());
        $this->assertSame(['id', 'level', 'category', 'log_time', 'message'], $tableSchema?->getColumnNames());
        $this->assertSame($this->idType, $tableSchema?->getColumn('id')->getType());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema?->getColumn('level')->getType());
        $this->assertSame(16, $tableSchema?->getColumn('level')->getSize());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema?->getColumn('category')->getType());
        $this->assertSame(255, $tableSchema?->getColumn('category')->getSize());
        $this->assertSame($this->logTime, $tableSchema?->getColumn('log_time')->getType());
        $this->assertSame($this->messageType, $tableSchema?->getColumn('message')->getType());

        DbHelper::dropTable($this->db, $tableWithPrefix);

        $this->assertNull($this->db->getTableSchema($tableWithPrefix, true));
    }

    public static function tableListWithPrefixProvider(): array
    {
        return [
            ['{{%test-table-1}}', 'test-table-1'],
            ['{{%test-table-2}}', 'test-table-2'],
        ];
    }

    public static function tableListIndexesProvider(): array
    {
        return [
            ['{{%custom-log}}', 'custom-log'],
            ['{{%test-table-1}}', 'test-table-1'],
            ['{{%test-table-2}}', 'test-table-2'],
        ];
    }
}
