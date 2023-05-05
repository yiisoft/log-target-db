<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Pgsql;

use Yiisoft\Log\Target\Db\Tests\Common\AbstractSQLDumpFileTest;
use Yiisoft\Log\Target\Db\Tests\Support\PgsqlFactory;

/**
 * @group pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SQLDumpFileTest extends AbstractSQLDumpFileTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlFactory())->createConnection();

        parent::setUp();
    }
}
