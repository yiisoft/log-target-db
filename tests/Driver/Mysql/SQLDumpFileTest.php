<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Mysql;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractSQLDumpFileTest;
use Yiisoft\Log\Target\Db\Tests\Support\MysqlFactory;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SQLDumpFileTest extends AbstractSQLDumpFileTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlFactory())->createConnection();

        parent::setUp();
    }
}
