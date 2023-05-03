/**
 * Database schema required by yiisoft/log-target-db for MSSQL.
 */
CREATE TABLE [dbo].[log] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DATETIME2(6) DEFAULT CURRENT_TIMESTAMP,
    [message] TEXT,
    CONSTRAINT [PK_log] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [IDX_log-category] ON [log] ([category]);
CREATE INDEX [IDX_log-level] ON [log] ([level]);
CREATE INDEX [IDX_log-log_time] ON [log] ([log-time]);
