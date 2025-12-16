<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Schema\SchemaInterface;

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
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function ensureTable(string $table = '{{%yii_log}}'): void
    {
        $driverName = $this->db->getDriverName();
        $schema = $this->db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

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
            'sqlsrv' => $schema->createColumn('DATETIME2(6)')->defaultValue($defaultValue),
            default => $schema->createColumn(SchemaInterface::TYPE_TIMESTAMP, 6)->defaultValue($defaultValue),
        };

        // `id` AutoIncrement custom for all dbms
        $id = match ($driverName) {
            'mysql' => $schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('AUTO_INCREMENT'),
            'pgsql' => $schema->createColumn('BIGSERIAL')->notNull(),
            'sqlsrv' => $schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('IDENTITY'),
            default => $schema->createColumn(SchemaInterface::TYPE_INTEGER)->notNull(),
        };

        // create table
        $this->db->createCommand()->createTable(
            $table,
            [
                'id' => $id,
                'level' => $schema->createColumn(SchemaInterface::TYPE_STRING, 16),
                'category' => $schema->createColumn(SchemaInterface::TYPE_STRING),
                'log_time' => $logTimeType,
                'message' => $schema->createColumn(SchemaInterface::TYPE_TEXT),
                "CONSTRAINT [[PK_$tableRawName]] PRIMARY KEY ([[id]])",
            ],
        )->execute();

        if ($driverName === 'oci') {
            $this->addSequenceAndTrigger($tableRawName);
        }

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
        $schema = $this->db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

        // drop table
        if ($this->db->getTableSchema($table, true) !== null) {
            $this->db->createCommand()->dropTable($tableRawName)->execute();

            // drop sequence oracle
            if ($this->db->getDriverName() === 'oci') {
                $this->db
                    ->createCommand()
                    ->setSql(
                        <<<SQL
                        DROP SEQUENCE {{{$tableRawName}_SEQ}}
                        SQL,
                    )
                    ->execute();
            }
        }
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    private function addSequenceAndTrigger(string $tableRawName): void
    {
        // create sequence oracle
        $this->db
            ->createCommand()
            ->setSql(
                <<<SQL
                CREATE SEQUENCE {{{$tableRawName}_SEQ}}
                START WITH 1
                INCREMENT BY 1
                NOMAXVALUE
                SQL,
            )
            ->execute();

        // create trigger oracle
        $this->db
            ->createCommand()
            ->setSql(
                <<<SQL
                CREATE TRIGGER {{{$tableRawName}_TRG}} BEFORE INSERT ON {{{$tableRawName}}} FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
                IF INSERTING AND :NEW."id" IS NULL THEN SELECT {{{$tableRawName}_SEQ}}.NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
                END COLUMN_SEQUENCES;
                END;
                SQL,
            )
            ->execute();
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
