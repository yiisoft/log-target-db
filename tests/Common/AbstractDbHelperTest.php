<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target\Db\DbHelper;

abstract class AbstractDbHelperTest extends TestCase
{
    protected ConnectionInterface $db;

    protected function setup(): void
    {
        DbHelper::ensureTable($this->db);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        DbHelper::dropTable($this->db);

        $this->db->close();

        unset($this->db);

        parent::tearDown();
    }

    public function testDropTable(): void
    {
        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%log}}', true));
    }

    public function testEnsureTable(): void
    {
        $table = '{{%log}}';

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTableExist(): void
    {
        $table = '{{%log}}';

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table));

        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema($table));
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('log', $this->db->getSchema()->getRawTableName('{{%log}}'));
    }
}
