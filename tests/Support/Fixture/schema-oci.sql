/**
 * Database schema required by yiisoft/log-target-db db for Oracle.
 */
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "test-table-1"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "test-table-2"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "test-table-1_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "test-table-2_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--

/* STATEMENTS */

CREATE TABLE "test-table-1"
(
    "id" NUMBER(20) NOT NULL PRIMARY KEY,
    "level" VARCHAR2(16),
    "category" VARCHAR2(255),
    "log_time" TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP,
    "message" CLOB
);

CREATE SEQUENCE "test-table-1_SEQ" START WITH 1 INCREMENT BY 1;
CREATE INDEX "CN_test-table-1-log-category" ON "test-table-1" ("category");
CREATE INDEX "CN_test-table-1-log-level" ON "test-table-1" ("level");

CREATE TABLE "test-table-2"
(
    "id" NUMBER(20) NOT NULL PRIMARY KEY,
    "level" VARCHAR2(16),
    "category" VARCHAR2(255),
    "log_time" TIMESTAMP(6) DEFAULT CURRENT_TIMESTAMP,
    "message" CLOB
);

CREATE SEQUENCE "test-table-2_SEQ" START WITH 1 INCREMENT BY 1;
CREATE INDEX "CN_test-table-2-log-category" ON "test-table-2" ("category");
CREATE INDEX "CN_test-table-2-log-level" ON "test-table-2" ("level");

/* TRIGGERS */

CREATE TRIGGER "test-table-1_TRG" BEFORE INSERT ON "test-table-1" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "test-table-1_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "test-table-2_TRG" BEFORE INSERT ON "test-table-2" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "test-table-2_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/

/* TRIGGERS */
