<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Migration;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\Db\DbTarget;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

use function md5;

/**
 * Creates log table.
 */
final class M202101052207CreateLog implements RevertibleMigrationInterface
{
    /**
     * @var DbTarget[] Targets for creating a log table.
     */
    private array $targets = [];

    public function __construct(LoggerInterface $logger)
    {
        if (!($logger instanceof Logger)) {
            throw new RuntimeException(
                'Implementation of the "\Psr\Log\LoggerInterface" must be an instance of the "\Yiisoft\Log\Logger".'
            );
        }

        foreach ($logger->getTargets() as $target) {
            if ($target instanceof DbTarget) {
                $this->targets[md5($target->getDb()->getDsn() . ':' . $target->getTable())] = $target;
            }
        }

        if ($this->targets === []) {
            throw new RuntimeException(
                'You should configure "\Yiisoft\Log\Logger" instance to use'
                . ' one or more database targets before executing this migration.'
            );
        }
    }

    public function up(MigrationBuilder $b): void
    {
        foreach ($this->targets as $target) {
            $builder = new MigrationBuilder($target->getDb());

            $builder->createTable($target->getTable(), [
                'id' => $builder->bigPrimaryKey(),
                'level' => $builder->string(16),
                'category' => $builder->string(),
                'log_time' => $builder->double(),
                'message' => $builder->text(),
            ]);

            $builder->createIndex('idx_log_level', $target->getTable(), 'level');
            $builder->createIndex('idx_log_category', $target->getTable(), 'category');
        }
    }

    public function down(MigrationBuilder $b): void
    {
        foreach ($this->targets as $target) {
            $builder = new MigrationBuilder($target->getDb());
            $builder->dropTable($target->getTable());
        }
    }
}
