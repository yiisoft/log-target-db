<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Log\Tests;

use Psr\Log\LogLevel;
use yii\console\ExitCode;
use yii\console\tests\unit\controllers\EchoMigrateController;
use yii\db\Connection;
use yii\db\Query;
use Yiisoft\Log\DbTarget;
use Yiisoft\Log\Logger;
use yii\tests\TestCase;

/**
 * @group db
 * @group log
 */
abstract class DbTargetTest extends TestCase
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected static $db;

    protected static $logTable = '{{%log}}';

    protected function runConsoleAction($route, $params = [])
    {
        $this->destroyApplication();
        $this->mockApplication([
            'id' => 'Migrator',
            'basePath' => '@yii/tests',
            'controllerMap' => [
                'migrate' => EchoMigrateController::class,
            ],
        ], null, [
            'logger' => function ($container) {
                $db = new DbTarget($container->get('db'));
                $db->levels = [LogLevel::WARNING];
                $db->logTable = '{{%log}}';
                return new Logger(['db' => $db]);
            },
            'db' => static::getConnection(),
        ]);

        ob_start();
        $result = $this->app->runAction($route, $params);
        echo 'Result is ' . $result;
        if ($result !== ExitCode::OK) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    public function setUp()
    {
        parent::setUp();
        $databases = static::getParam('databases');
        static::$database = $databases[static::$driverName];
        $pdo_database = 'pdo_' . static::$driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        $this->runConsoleAction('migrate/up', ['migrationPath' => '@Yii/Log/migrations/', 'interactive' => false]);
    }

    public function tearDown()
    {
        self::getConnection()->createCommand()->truncateTable(self::$logTable)->execute();
        $this->runConsoleAction('migrate/down', ['migrationPath' => '@Yii/Log/migrations/', 'interactive' => false]);
        if (static::$db) {
            static::$db->close();
        }
        parent::tearDown();
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\db\Exception
     * @throws \yii\exceptions\InvalidConfigException
     * @throws \yii\exceptions\InvalidArgumentException
     */
    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection();
            $db->dsn = static::$database['dsn'];
            if (isset(static::$database['username'])) {
                $db->username = static::$database['username'];
                $db->password = static::$database['password'];
            }
            if (isset(static::$database['attributes'])) {
                $db->attributes = static::$database['attributes'];
            }
            if (!$db->isActive) {
                $db->open();
            }
            static::$db = $db;
        }

        return static::$db;
    }

    /**
     * Tests that precision isn't lost for log timestamps.
     * @see https://github.com/yiisoft/yii2/issues/7384
     */
    public function testTimestamp()
    {
        $logger = $this->app->getLogger();

        $time = 1424865393.0105;

        // forming message data manually in order to set time
        $messsageData = [
            LogLevel::WARNING,
            'test',
            [
                'category' => 'test',
                'time' => $time,
                'trace' => [],
            ]
        ];

        $logger->messages[] = $messsageData;
        $logger->flush(true);

        $query = (new Query())->select('log_time')->from(self::$logTable)->where(['category' => 'test']);
        $loggedTime = $query->createCommand(self::getConnection())->queryScalar();
        $this->assertEquals($time, $loggedTime);
    }

    public function testTransactionRollBack()
    {
        $db = self::getConnection();
        $logger = $this->app->getLogger();

        $tx = $db->beginTransaction();

        $messsageData = [
            LogLevel::WARNING,
            'test',
            [
                'category' => 'test',
                'time' => time(),
                'trace' => [],
            ]
        ];

        $logger->messages[] = $messsageData;
        $logger->flush(true);

        // current db connection should still have a transaction
        $this->assertNotNull($db->transaction);
        // log db connection should not have transaction
        $this->assertNull($this->app->getLogger()->getTargets()['db']->db->transaction);

        $tx->rollBack();

        $count = (new Query())
            ->from(self::$logTable)
            ->where(['category' => 'test', 'message' => 'test'])
            ->count();
        static::assertEquals(1, $count);
    }
}
