<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db;

use RuntimeException;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Target;

use function microtime;
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
     * @var ConnectionInterface|null The database connection instance.
     */
    private ?ConnectionInterface $db = null;

    /**
     * @var DbFactory Factory for creating a database connection instance.
     * Provides lazy loading of the "Yiisoft\Db\Connection\ConnectionInterface" instance
     * to prevent a circular reference to the connection when building container definitions.
     */
    private DbFactory $factory;

    /**
     * @var string The name of the database table to store the log messages. Defaults to "log".
     */
    private string $table;

    /**
     *
     * @param DbFactory $factory Factory for creating a database connection instance.
     * Provides lazy loading of the "Yiisoft\Db\Connection\ConnectionInterface" instance
     * to prevent a circular reference to the connection when building container definitions.
     * @param string $table The name of the database table to store the log messages. Defaults to "log".
     */
    public function __construct(DbFactory $factory, string $table = '{{%log}}')
    {
        $this->factory = $factory;
        $this->table = $table;
        parent::__construct();
    }

    /**
     * Gets an instance of a database connection.
     *
     * @return ConnectionInterface
     */
    public function getDb(): ConnectionInterface
    {
        if ($this->db === null) {
            $this->db = $this->factory->create();
        }

        return $this->db;
    }

    /**
     * Gets the name of the database table to store the log messages.
     *
     * @return string
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
        $table = $this->getDb()->getSchema()->quoteTableName($this->table);

        $sql = "INSERT INTO {$table} ([[level]], [[category]], [[log_time]], [[message]])"
            . " VALUES (:level, :category, :log_time, :message)";

        try {
            $command = $this->getDb()->createCommand($sql);

            foreach ($this->getMessages() as $key => $message) {
                if ($command->bindValues([
                        ':level' => $message->level(),
                        ':category' => $message->context('category', ''),
                        ':log_time' => $message->context('time', $defaultLogTime),
                        ':message' => $formattedMessages[$key],
                    ])->execute() > 0) {
                    continue;
                }
                throw new RuntimeException(sprintf(
                    'The log message is not written to the database "%s;table:%s".',
                    $this->getDb()->getDsn(),
                    $table,
                ));
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to export log through database.', 0, $e);
        }
    }
}
