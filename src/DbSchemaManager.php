<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Schema\Quoter;

/**
 * Manages the log table schema in the database.
 */
final class DbSchemaManager
{
    public function __construct(private readonly ConnectionInterface $db)
    {
    }

    /**
     * Ensures that the log table exists in the database.
     *
     * @param string $table The name of the log table. Defaults to '{{%yii_log}}'.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function ensureTable(string $table = '{{%yii_log}}'): void
    {
        $driverName = $this->db->getDriverName();
        $columnBuilderClass = $this->db->getColumnBuilderClass();
        $tableRawName = $this->db->getQuoter()->getRawTableName($table);

        if ($this->hasTable($table)) {
            return;
        }

        // `log_Time` Default value custom for all dbms
        $defaultValue = match ($driverName) {
            'mysql' => new Expression('CURRENT_TIMESTAMP(6)'),
            'sqlite' => new Expression("(STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC'))"),
            default => new Expression('CURRENT_TIMESTAMP'),
        };

        // `log_Time` Type custom for all dbms
        $logTimeType = match ($driverName) {
            'sqlsrv' => $columnBuilderClass::structured('DATETIME2(6)'),
            default => $columnBuilderClass::timestamp( 6),
        };

        // `id` AutoIncrement custom for all dbms
        $id = match ($driverName) {
            'mysql' => $columnBuilderClass::bigint()->notNull()->autoIncrement(),
            'pgsql' => $columnBuilderClass::structured('BIGSERIAL')->notNull(),
            'sqlsrv' => $columnBuilderClass::bigint()->notNull()->extra('IDENTITY'),
            default => $columnBuilderClass::integer()->notNull(),
        };

        // create table
        $this->db->createCommand()->createTable(
            $table,
            [
                'id' => $id,
                'level' => $columnBuilderClass::string(16),
                'category' => $columnBuilderClass::string(),
                'log_time' => $logTimeType->defaultValue($defaultValue),
                'message' => $columnBuilderClass::text(),
                "CONSTRAINT [[PK_$tableRawName]] PRIMARY KEY ([[id]])",
            ],
        )->execute();

        $this->db->createCommand()->createIndex($table, "IDX_{$tableRawName}-category", 'category')->execute();
        $this->db->createCommand()->createIndex($table, "IDX_{$tableRawName}-level", 'level')->execute();
        $this->db->createCommand()->createIndex($table, "IDX_{$tableRawName}-time", 'log_time')->execute();
    }

    /**
     * Ensures that the log table does not exist in the database.
     *
     * @param string $table The name of the log table. Defaults to '{{%yii_log}}'.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function ensureNoTable(string $table = '{{%yii_log}}'): void
    {
        /** @var Quoter $quoter */
        $quoter = $this->db->getQuoter();
        $tableRawName = $quoter->getRawTableName($table);

        // drop table
        if ($this->db->getTableSchema($table, true) !== null) {
            $this->db->createCommand()->dropTable($tableRawName)->execute();
        }
    }

    /**
     * Checks if the given table exists in the database.
     *
     * @param string $table The name of the table to check.
     *
     * @return bool Whether the table exists or not.
     */
    private function hasTable(string $table): bool
    {
        return $this->db->getTableSchema($table, true) !== null;
    }
}
