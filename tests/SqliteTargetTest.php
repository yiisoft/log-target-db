<?php

declare(strict_types=1);

namespace Yiisoft\Log\Tests;

/**
 * @group db
 * @group sqlite
 * @group log
 */
class SqliteTargetTest extends DbTargetTest
{
    protected static $driverName = 'sqlite';

    public function testTransactionRollBack()
    {
        if (self::getConnection()->dsn === 'sqlite::memory:') {
            $this->markTestSkipped('It is not possible to test logging during transaction when the DB is in memory');
            return;
        }

        parent::testTransactionRollBack();
    }
}
