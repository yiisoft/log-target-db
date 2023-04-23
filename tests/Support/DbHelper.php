<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Db\Tests\Support;

use Yiisoft\Db\Driver\Pdo\PdoConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

use function explode;
use function file_get_contents;
use function trim;

final class DbHelper
{
    /**
     * Loads the fixture into the database.
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function loadFixture(PdoConnectionInterface $db, string ...$fixtures): void
    {
        $db->open();

        foreach ($fixtures as $fixture) {
            if ($db->getDriverName() === 'oci') {
                [$drops, $creates] = explode('/* STATEMENTS */', file_get_contents($fixture), 2);
                [$statements, $triggers, $data] = explode('/* TRIGGERS */', $creates, 3);
                $lines = array_merge(
                    explode('--', $drops),
                    explode(';', $statements),
                    explode('/', $triggers),
                    explode(';', $data)
                );
            } else {
                $lines = explode(';', file_get_contents($fixture));
            }

            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $db->getPDO()?->exec($line);
                }
            }
        }
    }
}
