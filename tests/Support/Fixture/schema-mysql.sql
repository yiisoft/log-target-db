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

CREATE INDEX `IDX_test-table-1-category` ON `test-table-1` (`category`);
CREATE INDEX `IDX_test-table-1-level` ON `test-table-1` (`level`);
CREATE INDEX `IDX_test-table-1-time` ON `test-table-1` (`log_time`);

CREATE TABLE `test-table-2` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `level` VARCHAR(16),
    `category` VARCHAR(255),
    `log_time` TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    `message` TEXT,
    CONSTRAINT `test-table-2-pk` PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX `IDX_test-table-2-category` ON `test-table-2` (`category`);
CREATE INDEX `IDX_test-table-2-level` ON `test-table-2` (`level`);
CREATE INDEX `IDX_test-table-2-time` ON `test-table-2` (`log_time`);
