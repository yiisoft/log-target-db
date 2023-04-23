/**
 * Database schema required by yiisoft/log-target-db for MSSQL.
 */
IF OBJECT_ID('[dbo].[log]', 'U') IS NOT NULL DROP TABLE [dbo].[log];

CREATE TABLE [dbo].[log] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DECIMAL(15, 4),
    [message] TEXT,
    CONSTRAINT [PK_log] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [idx-log-category] ON [log] ([category]);
CREATE INDEX [idx-log-level] ON [log] ([level]);
