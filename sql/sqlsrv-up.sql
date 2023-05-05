CREATE TABLE [yii_log] (
	[id] bigint NOT NULL IDENTITY,
	[level] nvarchar(16),
	[category] nvarchar(255),
	[log_time] DATETIME2(6) DEFAULT CURRENT_TIMESTAMP,
	[message] nvarchar(max),
	CONSTRAINT [PK_yii_log] PRIMARY KEY ([id])
);
CREATE INDEX [IDX_yii_log-category] ON [yii_log] ([category]);
CREATE INDEX [IDX_yii_log-level] ON [yii_log] ([level]);
CREATE INDEX [IDX_yii_log-time] ON [yii_log] ([log_time]);
