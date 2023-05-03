/**
 * Database schema required by yiisoft/log-target-db for MySQL.
 */
CREATE TABLE `log` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    `message` TEXT,
    CONSTRAINT `log_pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `IDX_log-category` ON `log` (`category`);
CREATE INDEX `IDX_log-level` ON `log` (`level`);
CREATE INDEX `IDX_log-log_time` ON `log` (`log-time`);
