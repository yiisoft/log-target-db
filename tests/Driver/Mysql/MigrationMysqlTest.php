<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Mysql;

use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMysqlTest extends AbstractMigrationTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        $this->db = (new MysqlHelper())->createConnection();
        $this->dbFixture = (new MysqlHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-mysql.sql'
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
        $indexes = $schema->getTableIndexes($table, true);

        sort($indexes);

        $this->assertSame(['category'], $indexes[0]->getColumnNames());
        $this->assertSame("IDX_$table-category", $indexes[0]->getName());
        $this->assertFalse($indexes[0]->isUnique());
        $this->assertFalse($indexes[0]->isPrimary());

        if (PHP_VERSION_ID >= 80100) {
            $this->assertSame(['id'], $indexes[1]->getColumnNames());
            $this->assertTrue($indexes[1]->isUnique());
            $this->assertTrue($indexes[1]->isPrimary());

            $this->assertSame(['level'], $indexes[2]->getColumnNames());
            $this->assertSame("IDX_$table-level", $indexes[2]->getName());
            $this->assertFalse($indexes[2]->isUnique());
            $this->assertFalse($indexes[2]->isPrimary());

            $this->assertSame(['log_time'], $indexes[3]->getColumnNames());
            $this->assertSame("IDX_$table-time", $indexes[3]->getName());
            $this->assertFalse($indexes[3]->isUnique());
            $this->assertFalse($indexes[3]->isPrimary());
        } else {
            $this->assertSame(['level'], $indexes[1]->getColumnNames());
            $this->assertSame("IDX_$table-level", $indexes[1]->getName());
            $this->assertFalse($indexes[1]->isUnique());
            $this->assertFalse($indexes[1]->isPrimary());

            $this->assertSame(['log_time'], $indexes[2]->getColumnNames());
            $this->assertSame("IDX_$table-time", $indexes[2]->getName());
            $this->assertFalse($indexes[2]->isUnique());
            $this->assertFalse($indexes[2]->isPrimary());

            $this->assertSame(['id'], $indexes[3]->getColumnNames());
            $this->assertTrue($indexes[3]->isUnique());
            $this->assertTrue($indexes[3]->isPrimary());
        }
    }
}
