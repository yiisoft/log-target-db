/**
 * Database schema required by yiisoft/log-target-db for PostgreSQL.
 */
DROP TABLE IF EXISTS "log";

CREATE TABLE "log"
(
    id BIGSERIAL NOT NULL PRIMARY KEY,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time DOUBLE PRECISION,
    message TEXT
);

CREATE INDEX "idx-log-category" ON "log" ("category");
CREATE INDEX "idx-log-level" ON "log" ("level");
