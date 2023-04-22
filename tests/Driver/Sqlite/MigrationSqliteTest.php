<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Sqlite;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\SqliteHelper;

/**
 * @group sqlite
 */
final class MigrationSqliteTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new SqliteHelper())->createConnection();
        $this->dbFixture = (new SqliteHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-sqlite.sql'
        );

        parent::setUp();
    }

    /**
     * @dataProvider tableListProvider
     */
    public function testVerifyTableIndexes(string $table): void
    {
        $schema = $this->db->getSchema();

        $indexes = $schema->getTableIndexes($table);

        $this->assertSame(['level'], $indexes[0]->getColumnNames());
        $this->assertSame("idx-$table-log-level", $indexes[0]->getName());
        $this->assertFalse($indexes[0]->isUnique());
        $this->assertFalse($indexes[0]->isPrimary());

        $this->assertSame(['category'], $indexes[1]->getColumnNames());
        $this->assertSame("idx-$table-log-category", $indexes[1]->getName());
        $this->assertFalse($indexes[1]->isUnique());
        $this->assertFalse($indexes[1]->isPrimary());
    }
}
