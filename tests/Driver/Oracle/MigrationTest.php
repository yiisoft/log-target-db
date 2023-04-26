<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Target\Db\Migration;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationTest extends AbstractMigrationTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('oci_');

        parent::setUp();
    }

    /**
     * @dataProvider tableListProviderWithPrefix
     */
    public function testVerifyTableIndexes(string $tablewithPrefix, string $table): void
    {
        Migration::ensureTable($this->db, $tablewithPrefix);

        $schema = $this->db->getSchema();
        $table = $this->db->getTablePrefix() . $table;

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($tablewithPrefix, true);

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
