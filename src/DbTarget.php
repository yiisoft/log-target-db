<?php

declare(strict_types=1);

namespace Yiisoft\Log;

use Yiisoft\Db\Command;
use Yiisoft\Db\ConnectionInterface;
use Yiisoft\Db\Exception;
use Yiisoft\VarDumper\VarDumper;

/**
 * DbTarget stores log messages in a database table.
 *
 * The database connection is specified by [[db]]. Database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/log/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the name of the table used to store the data by setting [[logTable]].
 */
class DbTarget extends Target
{
    /**
     * @var ConnectionInterface the DB connection object or the application component ID of the DB connection.
     * After the DbTarget object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    private $db;
    /**
     * @var string name of the DB table to store cache content. Defaults to "log".
     */
    private $logTable;

    /**
     * Initializes the DbTarget component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     *
     * @param ConnectionInterface $db
     * @param string $logTable
     */
    public function __construct(ConnectionInterface $db, $logTable = '{{%log}}')
    {
        $this->db = $db;
        $this->logTable = $logTable;
    }

    /**
     * Stores log messages to DB.
     *
     * @throws Exception
     * @throws LogRuntimeException
     * @throws \Throwable
     */
    public function export(): void
    {
        if ($this->db->getTransaction()) {
            // create new database connection, if there is an open transaction
            // to ensure insert statement is not affected by a rollback
            $this->db = clone $this->db;
        }

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";

        /** @var Command $command */
        $command = $this->db->createCommand($sql);
        foreach ($this->getMessages() as $message) {
            [$level, $text, $context] = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string) $text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            if ($command->bindValues([
                ':level' => $level,
                ':category' => $context['category'],
                ':log_time' => $context['time'],
                ':prefix' => $this->getMessagePrefix($message),
                ':message' => $text,
            ])->execute() > 0) {
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database.');
        }
    }

    /**
     * @return ConnectionInterface
     */
    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * @return string
     */
    public function getLogTable(): string
    {
        return $this->logTable;
    }
}
