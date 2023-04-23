/**
 * Database schema required by yiisoft/log-target-db for MySQL.
 */
DROP TABLE IF EXISTS `log`;

CREATE TABLE `log` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` DOUBLE,
    `message` TEXT,
    CONSTRAINT `log_pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx-log-category` ON `log` (`category`);
CREATE INDEX `idx-log-level` ON `log` (`level`);
