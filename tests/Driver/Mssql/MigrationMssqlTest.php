<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Mssql;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 */
final class MigrationMssqlTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new MssqlHelper())->createConnection();
        $this->dbFixture = (new MssqlHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-mssql.sql'
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

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertSame("PK_$table", $indexes[0]->getName());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        $this->assertSame(['category'], $indexes[1]->getColumnNames());
        $this->assertSame("idx-$table-log-category", $indexes[1]->getName());
        $this->assertFalse($indexes[1]->isUnique());
        $this->assertFalse($indexes[1]->isPrimary());

        $this->assertSame(['level'], $indexes[2]->getColumnNames());
        $this->assertSame("idx-$table-log-level", $indexes[2]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());
    }
}
