CREATE TABLE `yii_log` (
	`id` integer,
	`level` varchar(16),
	`category` varchar(255),
	`log_time` timestamp DEFAULT (STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC')),
	`message` text,
	CONSTRAINT `PK_yii_log` PRIMARY KEY (`id`)
);
CREATE INDEX `IDX_yii_log-category` ON `yii_log` (`category`);
CREATE INDEX `IDX_yii_log-level` ON `yii_log` (`level`);
CREATE INDEX `IDX_yii_log-time` ON `yii_log` (`log_time`);
