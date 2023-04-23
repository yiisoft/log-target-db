<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Pgsql;

use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\PgsqlHelper;

/**
 * @group pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationPgsqlTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new PgsqlHelper())->createConnection();

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

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertSame("PK_$table", $indexes[0]->getName());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        $this->assertSame(['category'], $indexes[1]->getColumnNames());
        $this->assertSame("IDX_$table-category", $indexes[1]->getName());
        $this->assertFalse($indexes[1]->isUnique());
        $this->assertFalse($indexes[1]->isPrimary());

        $this->assertSame(['level'], $indexes[2]->getColumnNames());
        $this->assertSame("IDX_$table-level", $indexes[2]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());

        $this->assertSame(['log_time'], $indexes[3]->getColumnNames());
        $this->assertSame("IDX_$table-time", $indexes[3]->getName());
        $this->assertFalse($indexes[3]->isUnique());
        $this->assertFalse($indexes[3]->isPrimary());
    }
}
