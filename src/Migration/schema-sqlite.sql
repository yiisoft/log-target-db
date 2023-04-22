/**
 * Database schema required by yiisoft/log-target-db db for SQLite.
 */
DROP TABLE IF EXISTS "log";

CREATE TABLE "log"
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time DOUBLE,
    message  TEXT
);

CREATE INDEX "idx-log-category" ON "log" ("category");
CREATE INDEX "idx-log-level" ON "log" ("level");
