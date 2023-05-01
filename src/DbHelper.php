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

use function in_array;
use function sprintf;

final class DbHelper
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public static function ensureTable(ConnectionInterface $db, string $table = '{{%log}}'): void
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

        self::validateSupportedDatabase($db);

        if ($schema->getTableSchema($table, true) !== null) {
            return;
        }

        // `log_Time` Default value custom for all dbms
        $defaultValue = match ($db->getDriverName()) {
            'mysql' => new Expression('CURRENT_TIMESTAMP(6)'),
            'sqlite' => new Expression("(STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC'))"),
            default => new Expression('CURRENT_TIMESTAMP'),
        };

        // `log_Time` Type custom for all dbms
        $logTimeType = match ($db->getDriverName()) {
            'sqlsrv' => $schema->createColumn('DATETIME2(6)')->defaultValue($defaultValue),
            default => $schema->createColumn(SchemaInterface::TYPE_TIMESTAMP, 6)->defaultValue($defaultValue),
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
                'log_time' => $logTimeType,
                'message' => $schema->createColumn(SchemaInterface::TYPE_TEXT),
                "CONSTRAINT [[PK_$tableRawName]] PRIMARY KEY ([[id]])",
            ],
        )->execute();

        if ($db->getDriverName() === 'oci') {
            self::addSequenceAndTrigger($command, $tableRawName);
        }

        $command->createIndex($table, "IDX_{$tableRawName}-category", 'category')->execute();
        $command->createIndex($table, "IDX_{$tableRawName}-level", 'level')->execute();
        $command->createIndex($table, "IDX_{$tableRawName}-time", 'log_time')->execute();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function dropTable(ConnectionInterface $db, string $table = '{{%log}}'): void
    {
        $command = $db->createCommand();
        $tableRawName = $db->getSchema()->getRawTableName($table);

        self::validateSupportedDatabase($db);

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

    /**
     * @throws Exception
     * @throws Throwable
     */
    private static function addSequenceAndTrigger(CommandInterface $command, string $tableRawName): void
    {
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

    private static function validateSupportedDatabase(ConnectionInterface $db): void
    {
        $driverName = $db->getDriverName();

        if (!in_array($driverName, ['mysql', 'oci', 'pgsql', 'sqlite', 'sqlsrv'], true)) {
            throw new NotSupportedException(
                sprintf(
                    'Database driver `%s` is not supported.',
                    $driverName,
                ),
            );
        }
    }
}
