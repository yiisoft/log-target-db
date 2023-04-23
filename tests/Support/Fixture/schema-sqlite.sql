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

CREATE INDEX "IDX_test-table-1-category" ON "test-table-1" ("category");
CREATE INDEX "IDX_test-table-1-level" ON "test-table-1" ("level");
CREATE INDEX "IDX_test-table-1-time" ON "test-table-1" ("log_time");

CREATE TABLE "test-table-2"
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time TIMESTAMP(6) DEFAULT (STRFTIME('%Y-%m-%d %H:%M:%f', 'NOW', 'UTC')),
    message  TEXT
);

CREATE INDEX "IDX_test-table-2-category" ON "test-table-2" ("category");
CREATE INDEX "IDX_test-table-2-level" ON "test-table-2" ("level");
CREATE INDEX "IDX_test-table-2-time" ON "test-table-2" ("log_time");
