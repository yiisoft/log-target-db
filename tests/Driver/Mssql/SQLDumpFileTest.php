<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Mssql;

use Yiisoft\Db\Constant\ColumnType;
use Yiisoft\Db\Schema\SchemaInterface;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractSQLDumpFileTest;
use Yiisoft\Log\Target\Db\Tests\Support\MssqlFactory;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SQLDumpFileTest extends AbstractSQLDumpFileTest
{
    protected string $logTime = ColumnType::DATETIME;
    protected string $messageType = ColumnType::STRING;

    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MssqlFactory())->createConnection();

        parent::setUp();
    }
}
