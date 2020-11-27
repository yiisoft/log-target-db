<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Migration;

use Psr\Log\LoggerInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Log\Target\Db\Exception\InvalidConfigException;
use Yiisoft\Yii\Db\Migration\Migration;

/**
 * Initializes log table.
 *
 * The indexes declared are not required. They are mainly used to improve the performance
 * of some queries about message levels and categories. Depending on your actual needs, you may
 * want to create additional indexes (e.g. index on `log_time`).
 */
class m141106_185632_log_init extends Migration
{
    /**
     * @var DbTarget[] Targets to create log table for
     */
    private array $dbTargets = [];

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ConnectionInterface $db)
    {
        $this->logger = $logger;
        parent::__construct($db);
    }

    /**
     * @throws InvalidConfigException
     *
     * @return DbTarget[]
     */
    protected function getDbTargets()
    {
        if ($this->dbTargets === []) {
            if (!$this->logger instanceof \Yiisoft\Log\Logger) {
                throw new InvalidConfigException('You should configure "logger" to be instance of "\Yiisoft\Log\Logger" before executing this migration.');
            }

            $usedTargets = [];
            foreach ($this->logger->getTargets() as $target) {
                if ($target instanceof DbTarget) {
                    $currentTarget = [
                        $target->getDb(),
                        $target->getLogTable(),
                    ];
                    if (!in_array($currentTarget, $usedTargets, true)) {
                        // do not create same table twice
                        $usedTargets[] = $currentTarget;
                        $this->dbTargets[] = $target;
                    }
                }
            }

            if ($this->dbTargets === []) {
                throw new InvalidConfigException('You should configure "log" component to use one or more database targets before executing this migration.');
            }
        }

        return $this->dbTargets;
    }

    public function up(): void
    {
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->getDb();

            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable($target->getLogTable(), [
                'id' => $this->bigPrimaryKey(),
                'level' => $this->string(),
                'category' => $this->string(),
                'log_time' => $this->double(),
                'prefix' => $this->text(),
                'message' => $this->text(),
            ], $tableOptions);

            $this->createIndex('idx_log_level', $target->getLogTable(), 'level');
            $this->createIndex('idx_log_category', $target->getLogTable(), 'category');
        }
    }

    public function down(): void
    {
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->getDb();

            $this->dropTable($target->getLogTable());
        }
    }
}
