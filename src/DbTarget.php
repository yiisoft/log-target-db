<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use RuntimeException;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target;

use function sprintf;

/**
 * DbTarget stores log messages in a database table.
 *
 * Database schema could be initialized by applying migration:
 * {@see \Yiisoft\Log\Target\Db\Migration\M202101052207CreateLog}.
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 */
final class DbTarget extends Target
{
    /**
     * @var ConnectionInterface The database connection instance.
     */
    private ConnectionInterface $db;

    /**
     * @var string The name of the database table to store the cache content. Defaults to "log".
     */
    private string $logTable;

    /**
     *
     * @param ConnectionInterface $db The database connection instance.
     * @param string $logTable The name of the database table to store the cache content. Defaults to "log".
     */
    public function __construct(ConnectionInterface $db, $logTable = '{{%log}}')
    {
        $this->db = $db;
        $this->logTable = $logTable;
        parent::__construct();
    }

    /**
     * Gets an instance of a database connection.
     *
     * @return ConnectionInterface
     */
    public function getDb(): ConnectionInterface
    {
        return $this->db;
    }

    /**
     * Gets the name of the database table to store the cache content.
     *
     * @return string
     */
    public function getLogTable(): string
    {
        return $this->logTable;
    }

    /**
     * Stores log messages to the database.
     *
     * @throws RuntimeException If the log cannot be exported.
     */
    protected function export(): void
    {
        $formattedMessages = $this->getFormattedMessages();
        $tableName = $this->db->getSchema()->quoteTableName($this->logTable);

        $sql = "INSERT INTO {$tableName} ([[level]], [[category]], [[log_time]], [[message]])
                VALUES (:level, :category, :log_time, :message)";

        try {
            $command = $this->db->createCommand($sql);

            foreach ($this->getMessages() as $key => $message) {
                if ($command->bindValues([
                        ':level' => $message->level(),
                        ':category' => $message->context('category'),
                        ':log_time' => $message->context('time'),
                        ':message' => $formattedMessages[$key],
                    ])->execute() > 0) {
                    continue;
                }
                throw new RuntimeException(sprintf(
                    'The log message is not written to the database "%s;table:%s".',
                    $this->db->getDsn(),
                    $tableName,
                ));
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to export log through database.', 0, $e);
        }
    }
}
