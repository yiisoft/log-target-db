<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests;

use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection as SqlLiteConnection;
use Yiisoft\Factory\Exceptions\InvalidConfigException;
use Yiisoft\Log\Target\Db\DbFactory;

final class DbFactoryTest extends TestCase
{
    public function testCreateFromArray(): void
    {
        $factory = new DbFactory($this->getContainer(), [
            '__class' => SqlLiteConnection::class,
            '__construct()' => [
                'dsn' => 'sqlite:' . self::DB_FILE,
            ],
        ]);
        $db = $factory->create();

        $this->assertInstanceOf(ConnectionInterface::class, $db);
        $this->assertSame('sqlite', $db->getDriverName());
    }

    public function testCreateFromCallable(): void
    {
        $factory = new DbFactory($this->getContainer(), function (ContainerInterface $container) {
            return $container->get(ConnectionInterface::class);
        });
        $db = $factory->create();

        $this->assertInstanceOf(ConnectionInterface::class, $db);
        $this->assertSame('sqlite', $db->getDriverName());
    }

    public function testCreateFromString(): void
    {
        $factory = new DbFactory($this->getContainer(), ConnectionInterface::class);
        $db = $factory->create();

        $this->assertInstanceOf(ConnectionInterface::class, $db);
        $this->assertSame('sqlite', $db->getDriverName());
    }

    public function testCreateFromStringWithPredefinedConnection(): void
    {
        $factory = new DbFactory($this->getContainer(), ConnectionInterface::class);
        $db = $factory->create();

        $this->assertInstanceOf(ConnectionInterface::class, $db);
        $this->assertSame('sqlite', $db->getDriverName());
    }

    public function invalidConnectionDefinitionProvider(): array
    {
        return [
            'object-class-not-connection-interface' => [new stdClass()],
            'string-class-not-connection-interface' => [stdClass::class],
        ];
    }

    /**
     * @dataProvider invalidConnectionDefinitionProvider
     *
     * @param mixed $config
     */
    public function testCreateThrowExceptionForInvalidConnectionDefinition($config): void
    {
        $factory = new DbFactory($this->getContainer(), $config);
        $this->expectException(RuntimeException::class);
        $factory->create();
    }

    public function invalidConfigurationProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'empty-array' => [[]],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidConfigurationProvider
     *
     * @param mixed $config
     */
    public function testConstructorThrowExceptionForInvalidConfiguration($config): void
    {
        $this->expectException(InvalidConfigException::class);
        new DbFactory($this->getContainer(), $config);
    }
}
