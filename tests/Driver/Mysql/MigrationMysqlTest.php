<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Mysql;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Log\Target\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 */
final class MigrationMysqlTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        $this->db = (new MysqlHelper())->createConnection();
        $this->dbFixture = (new MysqlHelper())->createConnection(
            fixture: dirname(__DIR__, 3) . '/src/Migration/schema-mysql.sql'
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

        sort($indexes);

        if (version_compare(PHP_VERSION, '8.1', '>=')) {
            $this->assertSame(['category'], $indexes[0]->getColumnNames());
            $this->assertSame("idx-$table-log-category", $indexes[0]->getName());
            $this->assertFalse($indexes[0]->isUnique());
            $this->assertFalse($indexes[0]->isPrimary());

            $this->assertSame(['id'], $indexes[1]->getColumnNames());
            $this->assertTrue($indexes[1]->isUnique());
            $this->assertTrue($indexes[1]->isPrimary());

            $this->assertSame(['level'], $indexes[2]->getColumnNames());
            $this->assertSame("idx-$table-log-level", $indexes[2]->getName());
            $this->assertFalse($indexes[2]->isUnique());
            $this->assertFalse($indexes[2]->isPrimary());
        } else {
            $this->assertSame(['category'], $indexes[0]->getColumnNames());
            $this->assertSame("idx-$table-log-category", $indexes[0]->getName());
            $this->assertFalse($indexes[0]->isUnique());
            $this->assertFalse($indexes[0]->isPrimary());

            $this->assertSame(['level'], $indexes[1]->getColumnNames());
            $this->assertSame("idx-$table-log-level", $indexes[1]->getName());
            $this->assertFalse($indexes[1]->isUnique());
            $this->assertFalse($indexes[1]->isPrimary());

            $this->assertSame(['id'], $indexes[2]->getColumnNames());
            $this->assertTrue($indexes[2]->isUnique());
            $this->assertTrue($indexes[2]->isPrimary());
        }
    }
}
