/**
 * Database schema required by yiisoft/cache db for MySQL.
 */
DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` DOUBLE,
    `message` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx-log-category` ON `log` (`category`);
CREATE INDEX `idx-log-level` ON `log` (`level`);
