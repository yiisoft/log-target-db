<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Factory\Definitions\DefinitionInterface;
use Yiisoft\Factory\Definitions\Normalizer;
use Yiisoft\Factory\Exceptions\InvalidConfigException;

use function is_object;
use function get_class;
use function gettype;
use function sprintf;

/**
 * DbFactory creates a database connection instance.
 *
 * Provides lazy loading of the {@see \Yiisoft\Db\Connection\ConnectionInterface} instance
 * to prevent a circular reference to the connection when building container definitions.
 */
final class DbFactory
{
    /**
     * @var ContainerInterface Container for creating a database connection instance.
     */
    private ContainerInterface $container;

    /**
     * @var DefinitionInterface Definition for creating a database connection instance.
     */
    private DefinitionInterface $definition;

    /**
     * @param ContainerInterface $container Container for creating a database connection instance.
     * @param mixed $config The configuration for creating a database connection instance.
     *
     * The configuration can be specified in one of the following forms:
     *
     * - A string: representing the class name of the object to be created.
     * - A configuration array: the array  must consist of `__class` contains name of the class to be instantiated,
     * `__construct()` holds an array of constructor arguments. The rest of the config and property values
     * and method calls. They are set/called in the order they appear in the array.
     * - A PHP callable: either an anonymous function or an array representing a class method
     * (`[$class or $object, $method]`). The callable should return a instance
     * of the {@see \Yiisoft\Db\Connection\ConnectionInterface}.
     *
     * @throws InvalidConfigException If the configuration is invalid.
     */
    public function __construct(ContainerInterface $container, $config)
    {
        $this->container = $container;
        $this->definition = Normalizer::normalize($config);
    }

    /**
     * Creates a database connection instance.
     *
     * @throws RuntimeException If the created object is not an instance of the `ConnectionInterface`.
     *
     * @return ConnectionInterface The database connection instance.
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     * @psalm-suppress DocblockTypeContradiction
     */
    public function create(): ConnectionInterface
    {
        $db = $this->definition->resolve($this->container);

        if (!($db instanceof ConnectionInterface)) {
            throw new RuntimeException(sprintf(
                'The "%s" is not an instance of the "Yiisoft\Db\Connection\ConnectionInterface".',
                (is_object($db) ? get_class($db) : gettype($db))
            ));
        }

        return $db;
    }
}
