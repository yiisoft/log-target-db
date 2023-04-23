/**
 * Database schema required by yiisoft/cache db for MySQL.
 */
DROP TABLE IF EXISTS `test-table-1`;
DROP TABLE IF EXISTS `test-table-2`;

CREATE TABLE `test-table-1` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    `message` TEXT,
    CONSTRAINT `test-table-1-pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx-test-table-1-log-category` ON `test-table-1` (`category`);
CREATE INDEX `idx-test-table-1-log-level` ON `test-table-1` (`level`);

CREATE TABLE `test-table-2` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    `message` TEXT,
    CONSTRAINT `test-table-2-pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `idx-test-table-2-log-category` ON `test-table-2` (`category`);
CREATE INDEX `idx-test-table-2-log-level` ON `test-table-2` (`level`);
