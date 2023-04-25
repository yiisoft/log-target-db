<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Sqlite;

use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\SqliteHelper;

/**
 * @group sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new SqliteHelper())->createConnection();

        parent::setUp();
    }

    public static function tableListProvider(): array
    {
        return array_merge(parent::tableListProvider(), [['log']]);
    }

    /**
     * @dataProvider tableListProvider
     */
    public function testVerifyTableIndexes(string $table): void
    {
        $schema = $this->db->getSchema();

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($table, true);

        $this->assertSame(['log_time'], $indexes[0]->getColumnNames());
        $this->assertSame("IDX_$table-time", $indexes[0]->getName());
        $this->assertFalse($indexes[0]->isUnique());
        $this->assertFalse($indexes[0]->isPrimary());

        $this->assertSame(['level'], $indexes[1]->getColumnNames());
        $this->assertSame("IDX_$table-level", $indexes[1]->getName());
        $this->assertFalse($indexes[1]->isUnique());
        $this->assertFalse($indexes[1]->isPrimary());

        $this->assertSame(['category'], $indexes[2]->getColumnNames());
        $this->assertSame("IDX_$table-category", $indexes[2]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());
    }
}
