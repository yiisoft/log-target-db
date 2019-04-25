<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
