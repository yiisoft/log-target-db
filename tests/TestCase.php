<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection as SqlLiteConnection;
use Yiisoft\Di\Container;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\Files\FileHelper;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Db\Migration\Helper\ConsoleHelper;

use function dirname;
use function file_exists;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private const DB_FILE = __DIR__ . '/runtime/test.sq3';

    private ?Container $container = null;

    protected function setUp(): void
    {
        $this->getContainer()->get(ConsoleHelper::class)->output()->setVerbosity(OutputInterface::VERBOSITY_QUIET);
    }

    protected function tearDown(): void
    {
        $db = $this->getContainer()->get(ConnectionInterface::class);

        foreach ($db->getSchema()->getTableNames() as $tableName) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        unset($this->container);

        if (file_exists(self::DB_FILE)) {
            FileHelper::unlink(self::DB_FILE);
        }
    }

    protected function getContainer(): Container
    {
        if ($this->container === null) {
            $this->container = new Container([
                Aliases::class => [
                    '@root' => dirname(__DIR__, 2),
                    '@runtime' => __DIR__ . '/runtime',
                    '@yiisoft/yii/db/migration' => '@root',
                ],

                CacheInterface::class => [
                    '__class' => Cache::class,
                    '__construct()' => [Reference::to(ArrayCache::class)],
                ],

                LoggerInterface::class => static fn (ContainerInterface $container) => new Logger([
                    new DbTarget($container->get(ConnectionInterface::class), 'test-table-1'),
                    new DbTarget($container->get(ConnectionInterface::class), 'test-table-2'),
                ]),

                ConnectionInterface::class => [
                    '__class' => SqlLiteConnection::class,
                    '__construct()' => [
                        'dsn' => 'sqlite:' . self::DB_FILE,
                    ],
                ],

                EventDispatcherInterface::class => Dispatcher::class,
                ListenerProviderInterface::class => Provider::class,
                ProfilerInterface::class => Profiler::class,
            ]);
        }

        return $this->container;
    }
}
