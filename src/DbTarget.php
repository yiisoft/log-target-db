<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use RuntimeException;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target;

use function microtime;

/**
 * DbTarget stores log messages in a database table.
 *
 * Database schema could be initialized by applying migration:
 * {@see \Yiisoft\Log\Target\Db\Migration\M202101052207CreateLog}.
 */
final class DbTarget extends Target
{
    /**
     * @param ConnectionInterface $db The database connection instance.
     * @param string $table The name of the database table to store the log messages. Defaults to "log".
     */
    public function __construct(private ConnectionInterface $db, private string $table = '{{%log}}')
    {
        parent::__construct();
    }

    /**
     * Gets an instance of a database connection.
     */
    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * Gets the name of the database table to store the log messages.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Stores log messages to the database.
     *
     * @throws RuntimeException If the log cannot be exported.
     */
    protected function export(): void
    {
        $defaultLogTime = microtime(true);
        $formattedMessages = $this->getFormattedMessages();
        $table = $this->db->getQuoter()->quoteTableName($this->table);

        $sql = "INSERT INTO {$table} ([[level]], [[category]], [[log_time]], [[message]])"
            . ' VALUES (:level, :category, :log_time, :message)';

        try {
            $command = $this->db->createCommand($sql);

            foreach ($this->getMessages() as $key => $message) {
                $command
                    ->bindValues(
                        [
                            ':level' => $message->level(),
                            ':category' => $message->context('category', ''),
                            ':log_time' => $message->context('time', $defaultLogTime),
                            ':message' => $formattedMessages[$key],
                        ]
                    )
                    ->execute();
            }
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
