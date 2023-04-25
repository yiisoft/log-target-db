<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Db\Schema\SchemaInterface;

final class DbHelper
{
    public static function ensureTable(ConnectionInterface $db, string $table): void
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

        if ($schema->getTableSchema($table, true) !== null) {
            return;
        }

        // `log_Time` Type custom for all dbms
        $logTimeType = match ($db->getDriverName()) {
            'sqlsrv' => 'DATETIME2(6)',
            default => 'TIMESTAMP(6)',
        };

        // `log_Time` Default value custom for all dbms
        $defaultValue = match ($db->getDriverName()) {
            'sqlite' => new Expression("(STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC'))"),
            default => new Expression('CURRENT_TIMESTAMP'),
        };

        // `id` AutoIncrement custom for all dbms
        $id = match ($db->getDriverName()) {
            'mysql' => $schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('AUTO_INCREMENT'),
            'oci' => $schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull(),
            'pgsql' => $schema->createColumn('BIGSERIAL')->notNull(),
            'sqlsrv' => $schema->createColumn(SchemaInterface::TYPE_BIGINT)->notNull()->append('IDENTITY'),
            default => $schema->createColumn(SchemaInterface::TYPE_INTEGER),
        };

        // create table
        $command->createTable(
            $table,
            [
                'id' => $id,
                'level' => $schema->createColumn(SchemaInterface::TYPE_STRING, 16),
                'category' => $schema->createColumn(SchemaInterface::TYPE_STRING),
                'log_time' => $schema->createColumn($logTimeType)->defaultValue($defaultValue),
                'message' => $schema->createColumn(SchemaInterface::TYPE_TEXT),
                "CONSTRAINT [[PK_{$tableRawName}]] PRIMARY KEY ([[id]])"
            ],
        )->execute();

        if ($db->getDriverName() === 'oci') {
            // create sequence oracle
            $command->setSql(
                <<<SQL
                CREATE SEQUENCE {{{$tableRawName}_SEQ}}
                START WITH 1
                INCREMENT BY 1
                NOMAXVALUE
                SQL,
            )->execute();

            // create trigger oracle
            $command->setSql(
                <<<SQL
                CREATE TRIGGER {{{$tableRawName}_TRG}} BEFORE INSERT ON {{{$tableRawName}}} FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
                IF INSERTING AND :NEW."id" IS NULL THEN SELECT {{{$tableRawName}_SEQ}}.NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
                END COLUMN_SEQUENCES;
                END;
                SQL,
            )->execute();
        }

        $command->createIndex($table, "IDX_{$tableRawName}-category", 'category')->execute();
        $command->createIndex($table, "IDX_{$tableRawName}-level", 'level')->execute();
        $command->createIndex($table, "IDX_{$tableRawName}-time", 'log_time')->execute();
    }

    public static function dropTable(ConnectionInterface $db, string $table): void
    {
        $command = $db->createCommand();
        $tableRawName = $db->getSchema()->getRawTableName($table);

        // drop table
        if ($db->getTableSchema($table, true) !== null) {
            $command->dropTable($table)->execute();

            // drop sequence oracle
            if ($db->getDriverName() === 'oci') {
                $command->setSql(
                    <<<SQL
                    DROP SEQUENCE {{{$tableRawName}_SEQ}}
                    SQL,
                )->execute();
            }
        }
    }
}
