CREATE TABLE "yii_log" (
	"id" BIGSERIAL NOT NULL,
	"level" varchar(16),
	"category" varchar(255),
	"log_time" timestamp(6) DEFAULT CURRENT_TIMESTAMP,
	"message" text,
	CONSTRAINT "PK_yii_log" PRIMARY KEY ("id")
);
CREATE INDEX "IDX_yii_log-category" ON "yii_log" ("category");
CREATE INDEX "IDX_yii_log-level" ON "yii_log" ("level");
CREATE INDEX "IDX_yii_log-time" ON "yii_log" ("log_time");
