/**
 * Database schema required by yiisoft/cache db for MSSQL.
 */
IF OBJECT_ID('[dbo].[test-table-1]', 'U') IS NOT NULL DROP TABLE [dbo].[test-table-1];
IF OBJECT_ID('[dbo].[test-table-2]', 'U') IS NOT NULL DROP TABLE [dbo].[test-table-2];

CREATE TABLE [dbo].[test-table-1] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DECIMAL(15, 4),
    [message] TEXT,
    CONSTRAINT [PK_test-table-1] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [idx-test-table-1-log-category] ON [test-table-1] ([category]);
CREATE INDEX [idx-test-table-1-log-level] ON [test-table-1] ([level]);

CREATE TABLE [dbo].[test-table-2] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DECIMAL(15, 4),
    [message] TEXT,
    CONSTRAINT [PK_test-table-2] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [idx-test-table-2-log-category] ON [test-table-2] ([category]);
CREATE INDEX [idx-test-table-2-log-level] ON [test-table-2] ([level]);
