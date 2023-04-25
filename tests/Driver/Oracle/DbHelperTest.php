<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Driver\Oracle;

use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Target\Db\Tests\Common\AbstractDbHelperTest;
use Yiisoft\Log\Target\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends AbstractDbHelperTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        $this->db = (new OracleHelper())->createConnection();

        parent::setUp();
    }
}
