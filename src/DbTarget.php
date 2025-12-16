<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use RuntimeException;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target;

/**
 * Stores log messages in a database table.
 *
 * Use {@see DbSchemaManager::ensureTable()} to initialize database schema.
 */
final class DbTarget extends Target
{
    /**
     * @param ConnectionInterface $db The database connection instance.
     * @param string $table The name of the database table to store the log messages. Defaults to "{{%yii_log}}".
     * @param string[] $levels The {@see \Psr\Log\LogLevel log message levels} that this target is interested in.
     */
    public function __construct(
        private readonly ConnectionInterface $db,
        private readonly string $table = '{{%yii_log}}',
        array $levels = []
    ) {
        parent::__construct($levels);
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
     * @throws RuntimeException If the log can't be exported.
     */
    protected function export(): void
    {
        $formattedMessages = $this->getFormattedMessages();
        $table = $this->db->getQuoter()->quoteTableName($this->table);

        try {
            $command = $this->db->createCommand();

            foreach ($this->getMessages() as $key => $message) {
                /** @psalm-var mixed $logTime */
                $logTime = $message->context('time');
                $columns = [
                    'level' => $message->level(),
                    'category' => $message->context('category', ''),
                    'log_time' => $logTime,
                    'message' => $formattedMessages[$key],
                ];

                if ($logTime === null) {
                    unset($columns['log_time']);
                }

                $command->insert($table, $columns)->execute();
            }
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
