<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Pgsql;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbSchemaManagerTest;
use Yiisoft\Log\Target\Db\Tests\Support\PgsqlFactory;

/**
 * @group pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbSchemaManagerTest extends AbstractDbSchemaManagerTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('pgsql_');

        parent::setUp();
    }
}
