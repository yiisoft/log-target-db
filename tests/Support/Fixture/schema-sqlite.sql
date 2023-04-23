/**
 * Database schema required by yiisoft/log-target-db db for SQLite.
 */
DROP TABLE IF EXISTS "test-table-1";
DROP TABLE IF EXISTS "test-table-2";

CREATE TABLE "test-table-1"
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time TIMESTAMP(6) DEFAULT (STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC')),
    message TEXT
);

CREATE INDEX "idx-test-table-1-log-category" ON "test-table-1" ("category");
CREATE INDEX "idx-test-table-1-log-level" ON "test-table-1" ("level");

CREATE TABLE "test-table-2"
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time TIMESTAMP(6) DEFAULT (STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC')),
    message  TEXT
);

CREATE INDEX "idx-test-table-2-log-category" ON "test-table-2" ("category");
CREATE INDEX "idx-test-table-2-log-level" ON "test-table-2" ("level");
