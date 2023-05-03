<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Mysql;

use Throwable;
use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Log\Target\Db\DbHelper;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbHelperTest;
use Yiisoft\Log\Target\Db\Tests\Support\MysqlFactory;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends AbstractDbHelperTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('mysql_');

        parent::setUp();
    }

    /**
     * @dataProvider tableListIndexesProvider
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(string $tableWithPrefix, string $table): void
    {
        DbHelper::ensureTable($this->db, $tableWithPrefix);

        $schema = $this->db->getSchema();
        $table = $this->db->getTablePrefix() . $table;

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($tableWithPrefix, true);

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

        DbHelper::dropTable($this->db, $tableWithPrefix);
    }
}
