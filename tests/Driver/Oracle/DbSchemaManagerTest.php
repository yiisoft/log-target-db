<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use Yiisoft\Db\Constant\ColumnType;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbSchemaManagerTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleFactory;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbSchemaManagerTest extends AbstractDbSchemaManagerTest
{
    protected string $idType = ColumnType::INTEGER;

    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('oci_');

        parent::setUp();
    }
}
