/**
 * Database schema required by yiisoft/cache db for MSSQL.
 */
IF OBJECT_ID('[dbo].[test-table-1]', 'U') IS NOT NULL DROP TABLE [dbo].[test-table-1];
IF OBJECT_ID('[dbo].[test-table-2]', 'U') IS NOT NULL DROP TABLE [dbo].[test-table-2];

CREATE TABLE [dbo].[test-table-1] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DATETIME2(6) DEFAULT CURRENT_TIMESTAMP,
    [message] TEXT,
    CONSTRAINT [PK_test-table-1] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [IDX_test-table-1-category] ON [test-table-1] ([category]);
CREATE INDEX [IDX_test-table-1-level] ON [test-table-1] ([level]);
CREATE INDEX [IDX_test-table-1-time] ON [test-table-1] ([log_time]);

CREATE TABLE [dbo].[test-table-2] (
    [id] BIGINT IDENTITY NOT NULL,
    [level] NVARCHAR(16),
    [category] NVARCHAR(255),
    [log_time] DATETIME2(6) DEFAULT CURRENT_TIMESTAMP,
    [message] TEXT,
    CONSTRAINT [PK_test-table-2] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE INDEX [IDX_test-table-2-category] ON [test-table-2] ([category]);
CREATE INDEX [IDX_test-table-2-level] ON [test-table-2] ([level]);
CREATE INDEX [IDX_test-table-2-time] ON [test-table-2] ([log_time]);
