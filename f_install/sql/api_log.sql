CREATE TABLE IF NOT EXISTS "api_log" (
	"id"	INTEGER NOT NULL,
	"user_id"	INTEGER NOT NULL,
	"ip"	TEXT NOT NULL,
	"date"	TEXT NOT NULL,
	"path"	TEXT NOT NULL,
	"arg"	TEXT,
	"aux"	TEXT,
	PRIMARY KEY("id" AUTOINCREMENT)
);
CREATE INDEX IF NOT EXISTS "by_date" ON "api_log" (
	"date"	DESC
);
CREATE INDEX IF NOT EXISTS "by_user" ON "api_log" (
	"user_id"	ASC,
	"date"	DESC
);
