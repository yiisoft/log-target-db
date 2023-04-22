<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Db\Cache\SchemaCache;

abstract class ConnectionHelper
{
    protected function createSchemaCache(): SchemaCache
    {
        return new SchemaCache(new ArrayCache());
    }
}
