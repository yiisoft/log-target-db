/**
 * Database schema required by yiisoft/log-target-db for SQLite.
 */
DROP TABLE IF EXISTS "log";

CREATE TABLE "log"
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time TIMESTAMP(6) DEFAULT (STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC')),
    message  TEXT
);

CREATE INDEX "IDX_log-category" ON "log" ("category");
CREATE INDEX "IDX_log-level" ON "log" ("level");
CREATE INDEX "IDX_log-log-time" ON "log" ("log-time");
