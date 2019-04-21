CREATE TABLE IF NOT EXISTS `err_log` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`date`	TEXT NOT NULL,
	`err`	TEXT NOT NULL,
	`errstr`	TEXT NOT NULL,
	`errpath`  TEXT NOT NULL,
	`trace`	TEXT NOT NULL,
	`webuser`  TEXT NULL
);
CREATE INDEX IF NOT EXISTS `date` ON `err_log` (
	`date`	DESC
);
