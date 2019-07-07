<?php
namespace Yiisoft\Log\Tests;

/**
 * @group db
 * @group pgsql
 * @group log
 */
class PgSQLTargetTest extends DbTargetTest
{
    protected static $driverName = 'pgsql';
}
