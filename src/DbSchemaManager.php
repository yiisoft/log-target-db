<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use Throwable;
use Yiisoft\Db\Command\CommandInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidArgumentException;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Schema\SchemaInterface;

final class DbSchemaManager
{
    private CommandInterface $command;
    private string $driverName = '';
    private SchemaInterface $schema;

    public function __construct(private ConnectionInterface $db)
    {
        $this->command = $db->createCommand();
        $this->driverName = $db->getDriverName();
        $this->schema = $db->getSchema();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function ensureTable(string $table = '{{%log}}'): void
    {
        $tableRawName = $this->schema->getRawTableName($table);

        if ($this->hasTable($table)) {
            return;
        }

        // `log_Time` Default value custom for all dbms
        $defaultValue = match ($this->driverName) {
            'mysql' => new Expression('CURRENT_TIMESTAMP(6)'),
            'sqlite' => new Expression("(STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC'))"),
            default => new Expression('CURRENT_TIMESTAMP'),
        };

        // `log_Time` Type custom for all dbms
        $logTimeType = match ($this->driverName) {
            'sqlsrv' => $this->schema->createColumn('DATETIME2(6)')->defaultValue($defaultValue),
            default => $this->schema->createColumn(SchemaInterface::TYPE_TIMESTAMP, 6)->defaultValue($defaultValue),
        };

        // `id` AutoIncrement custom for all dbms
        $id = match ($this->driverName) {
            'mysql' => $this->schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('AUTO_INCREMENT'),
            'oci' => $this->schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull(),
            'pgsql' => $this->schema->createColumn('BIGSERIAL')->notNull(),
            'sqlsrv' => $this->schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('IDENTITY'),
            default => $this->schema->createColumn(SchemaInterface::TYPE_INTEGER),
        };

        // create table
        $this->command->createTable(
            $table,
            [
                'id' => $id,
                'level' => $this->schema->createColumn(SchemaInterface::TYPE_STRING, 16),
                'category' => $this->schema->createColumn(SchemaInterface::TYPE_STRING),
                'log_time' => $logTimeType,
                'message' => $this->schema->createColumn(SchemaInterface::TYPE_TEXT),
                "CONSTRAINT [[PK_$tableRawName]] PRIMARY KEY ([[id]])",
            ],
        )->execute();

        if ($this->driverName === 'oci') {
            $this->addSequenceAndTrigger($tableRawName);
        }

        $this->command->createIndex($table, "IDX_{$tableRawName}-category", 'category')->execute();
        $this->command->createIndex($table, "IDX_{$tableRawName}-level", 'level')->execute();
        $this->command->createIndex($table, "IDX_{$tableRawName}-time", 'log_time')->execute();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function ensureNoTable(string $table = '{{%log}}'): void
    {
        $tableRawName = $this->schema->getRawTableName($table);

        // drop table
        if ($this->db->getTableSchema($table, true) !== null) {
            $this->command->dropTable($tableRawName)->execute();

            // drop sequence oracle
            if ($this->driverName === 'oci') {
                $this->command->setSql(
                    <<<SQL
                    DROP SEQUENCE {{{$tableRawName}_SEQ}}
                    SQL,
                )->execute();
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
        $this->command->setSql(
            <<<SQL
            CREATE SEQUENCE {{{$tableRawName}_SEQ}}
            START WITH 1
            INCREMENT BY 1
            NOMAXVALUE
            SQL,
        )->execute();

        // create trigger oracle
        $this->command->setSql(
            <<<SQL
            CREATE TRIGGER {{{$tableRawName}_TRG}} BEFORE INSERT ON {{{$tableRawName}}} FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
            IF INSERTING AND :NEW."id" IS NULL THEN SELECT {{{$tableRawName}_SEQ}}.NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
            END COLUMN_SEQUENCES;
            END;
            SQL,
        )->execute();
    }

    private function hasTable(string $table): bool
    {
        return $this->db->getTableSchema($table, true) !== null;
    }
}
