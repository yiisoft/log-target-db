/**
 * Database schema required by yiisoft/log-target-db for PostgreSQL.
 */
DROP TABLE IF EXISTS "log";

CREATE TABLE "log"
(
    id BIGSERIAL NOT NULL,
    level VARCHAR(16),
    category VARCHAR(255),
    log_time TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP(6),
    message TEXT,
    CONSTRAINT "PK_log" PRIMARY KEY (id)
);

CREATE INDEX "IDX_log-category" ON "log" ("category");
CREATE INDEX "IDX_log-level" ON "log" ("level");
CREATE INDEX "IDX_log-time" ON "log" ("log_time");
