/* STATEMENTS */
CREATE TABLE "yii_log" (
	"id" NUMBER(20) NOT NULL,
	"level" VARCHAR2(16),
	"category" VARCHAR2(255),
	"log_time" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	"message" CLOB,
	CONSTRAINT "PK_yii_log" PRIMARY KEY ("id")
);
CREATE SEQUENCE "yii_log_SEQ" START WITH 1 INCREMENT BY 1 NOMAXVALUE;
CREATE INDEX "IDX_yii_log-category" ON "yii_log" ("category");
CREATE INDEX "IDX_yii_log-level" ON "yii_log" ("level");
CREATE INDEX "IDX_yii_log-time" ON "yii_log" ("log_time");

/* TRIGGERS */
CREATE TRIGGER "yii_log_TRG" BEFORE INSERT ON "yii_log" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "yii_log_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/