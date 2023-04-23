<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationOracleTest extends AbstractMigrationTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        $this->db = (new OracleHelper())->createConnection();
        $this->dbFixture = (new OracleHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-oci.sql'
        );

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
        $indexes = $schema->getTableIndexes($table);

        $this->assertSame(['category'], $indexes[0]->getColumnNames());
        $this->assertSame("IDX_$table-category", $indexes[0]->getName());
        $this->assertFalse($indexes[0]->isUnique());
        $this->assertFalse($indexes[0]->isPrimary());

        $this->assertSame(['id'], $indexes[1]->getColumnNames());
        $this->assertSame("PK_$table", $indexes[1]->getName());
        $this->assertTrue($indexes[1]->isUnique());
        $this->assertTrue($indexes[1]->isPrimary());

        $this->assertSame(['log_time'], $indexes[2]->getColumnNames());
        $this->assertSame("IDX_$table-time", $indexes[2]->getName());
        $this->assertFalse($indexes[2]->isUnique());
        $this->assertFalse($indexes[2]->isPrimary());

        $this->assertSame(['level'], $indexes[3]->getColumnNames());
        $this->assertSame("IDX_$table-level", $indexes[3]->getName());
        $this->assertFalse($indexes[3]->isUnique());
        $this->assertFalse($indexes[3]->isPrimary());
    }
}