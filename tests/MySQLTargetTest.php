<?php

declare(strict_types=1);

namespace Yiisoft\Log\Tests;

/**
 * @group db
 * @group mysql
 * @group log
 */
class MySQLTargetTest extends DbTargetTest
{
    protected static $driverName = 'mysql';
}
