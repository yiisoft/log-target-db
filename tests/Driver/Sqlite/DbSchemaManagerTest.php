<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Sqlite;

use Yiisoft\Db\Constant\ColumnType;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbSchemaManagerTest;
use Yiisoft\Log\Target\Db\Tests\Support\SqliteFactory;

/**
 * @group sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbSchemaManagerTest extends AbstractDbSchemaManagerTest
{
    protected string $idType = ColumnType::INTEGER;

    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new SqliteFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('sqlite3_');

        parent::setUp();
    }
}
