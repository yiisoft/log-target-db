<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Oracle;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 */
final class MigrationOracleTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new OracleHelper())->createConnection();
        $this->dbFixture = (new OracleHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-oci.sql'
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
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        $this->assertSame(['level'], $indexes[1]->getColumnNames());
        $this->assertSame("CN_$table-log-level", $indexes[1]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());

        $this->assertSame(['category'], $indexes[2]->getColumnNames());
        $this->assertSame("CN_$table-log-category", $indexes[2]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());
    }
}
