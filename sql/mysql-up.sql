CREATE TABLE `yii_log` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`level` varchar(16),
	`category` varchar(255),
	`log_time` timestamp(6) DEFAULT CURRENT_TIMESTAMP(6),
	`message` text,
	CONSTRAINT `PK_yii_log` PRIMARY KEY (`id`)
);
CREATE INDEX `IDX_yii_log-category` ON `yii_log` (`category`);
CREATE INDEX `IDX_yii_log-level` ON `yii_log` (`level`);
CREATE INDEX `IDX_yii_log-time` ON `yii_log` (`log_time`);
