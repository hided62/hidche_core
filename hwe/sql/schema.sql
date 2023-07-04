##############################
## 장수 테이블
##############################
CREATE TABLE `general` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`owner` INT(11) NOT NULL DEFAULT '0',
	`npcmsg` TEXT NULL DEFAULT '',
	`npc` INT(1) NOT NULL DEFAULT '0',
	`npc_org` INT(1) NULL DEFAULT '0',
	`affinity` INT(3) NULL DEFAULT '0',
	`bornyear` INT(3) NULL DEFAULT '180',
	`deadyear` INT(3) NULL DEFAULT '300',
	`newmsg` INT(1) NULL DEFAULT '0',
	`con` INT(6) NOT NULL DEFAULT '0',
	`connect` INT(6) NOT NULL DEFAULT '0',
	`refresh` INT(6) NOT NULL DEFAULT '0',
	`logcnt` INT(6) NULL DEFAULT '1',
	`refcnt` INT(6) NULL DEFAULT '1',
	`picture` VARCHAR(40) NOT NULL,
	`imgsvr` INT(1) NOT NULL DEFAULT '0',
	`name` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_bin',
	`owner_name` VARCHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`nation` INT(6) NOT NULL DEFAULT '0',
	`city` INT(6) NOT NULL DEFAULT '3',
	`troop` INT(6) NOT NULL DEFAULT '0',
	`leadership` INT(3) NOT NULL DEFAULT '50',
	`leadership_exp` INT(3) NOT NULL DEFAULT '0',
	`strength` INT(3) NOT NULL DEFAULT '50',
	`strength_exp` INT(3) NOT NULL DEFAULT '0',
	`intel` INT(3) NOT NULL DEFAULT '50',
	`intel_exp` INT(3) NOT NULL DEFAULT '0',
	`injury` INT(2) NOT NULL DEFAULT '0',
	`experience` INT(6) NOT NULL DEFAULT '0',
	`dedication` INT(6) NOT NULL DEFAULT '0',
	`dex1` INT(8) NOT NULL DEFAULT '0',
	`dex2` INT(8) NOT NULL DEFAULT '0',
	`dex3` INT(8) NOT NULL DEFAULT '0',
	`dex4` INT(8) NOT NULL DEFAULT '0',
	`dex5` INT(8) NOT NULL DEFAULT '0',
	`officer_level` INT(2) NOT NULL DEFAULT '0',
	`officer_city` INT(4) NOT NULL DEFAULT '0',
	`permission` ENUM('normal', 'auditor', 'ambassador') NULL DEFAULT 'normal',
	`gold` INT(6) NOT NULL DEFAULT '1000',
	`rice` INT(6) NOT NULL DEFAULT '1000',
	`crew` INT(5) NOT NULL DEFAULT '0',
	`crewtype` INT(2) NOT NULL DEFAULT '0',
	`train` INT(3) NOT NULL DEFAULT '0',
	`atmos` INT(3) NOT NULL DEFAULT '0',
	`weapon` VARCHAR(20) NOT NULL DEFAULT 'None',
	`book` VARCHAR(20) NOT NULL DEFAULT 'None',
	`horse` VARCHAR(20) NOT NULL DEFAULT 'None',
	`item` VARCHAR(20) NOT NULL DEFAULT 'None',
	`turntime` DATETIME(6) NOT NULL,
	`recent_war` DATETIME(6) NULL DEFAULT NULL,
	`makelimit` INT(2) NULL DEFAULT '0',
	`killturn` INT(3) NULL DEFAULT NULL,
	`lastconnect` DATETIME NULL DEFAULT NULL,
	`lastrefresh` DATETIME NULL DEFAULT NULL,
	`ip` VARCHAR(40) NULL DEFAULT '',
	`block` INT(1) NULL DEFAULT '0',
	`dedlevel` INT(2) NULL DEFAULT '0',
	`explevel` INT(2) NULL DEFAULT '0',
	`age` INT(3) NULL DEFAULT '20',
	`startage` INT(3) NULL DEFAULT '20',
	`belong` INT(2) NULL DEFAULT '1',
	`betray` INT(2) NULL DEFAULT '0',
	`personal` VARCHAR(20) NOT NULL DEFAULT 'None',
	`special` VARCHAR(20) NOT NULL DEFAULT 'None',
	`specage` INT(2) NULL DEFAULT '0',
	`special2` VARCHAR(20) NOT NULL DEFAULT 'None',
	`specage2` INT(2) NULL DEFAULT '0',
	`defence_train` INT(3) NULL DEFAULT '80',
	`tnmt` INT(1) NULL DEFAULT '1',
	`myset` INT(1) NULL DEFAULT '6',
	`tournament` INT(1) NULL DEFAULT '0',
	`newvote` INT(1) NULL DEFAULT '0',
	`last_turn` TEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`aux` LONGTEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`penalty` TEXT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`no`),
	INDEX `nation` (`nation`, `npc`),
	INDEX `city` (`city`),
	INDEX `turntime` (`turntime`, `no`),
	INDEX `no_member` (`owner`),
	INDEX `npc` (`npc`),
	INDEX `troop` (`troop`, `turntime`),
	INDEX `officer_level` (`nation`, `officer_level`),
	INDEX `officer_city` (`officer_city`, `officer_level`),
	INDEX `name` (`name`),
	INDEX `last_refresh` (`lastrefresh`),
	CONSTRAINT `json1` CHECK (json_valid(`last_turn`)),
	CONSTRAINT `json2` CHECK (json_valid(`aux`)),
	CONSTRAINT `json3` CHECK (json_valid(`penalty`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
CREATE TABLE `general_turn` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`general_id` INT(11) NOT NULL,
	`turn_idx` INT(4) NOT NULL,
	`action` VARCHAR(20) NOT NULL,
	`arg` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`brief` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `general_id` (`general_id`, `turn_idx`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
CREATE TABLE `general_access_log` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`general_id` INT(11) NOT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`nation_id` INT(11) NOT NULL,
	`last_refresh` DATETIME NOT NULL,
	`last_connect` DATETIME NOT NULL,
	`login_total` INT(11) NOT NULL,
	`refresh` INT(11) NOT NULL,
	`refresh_total` INT(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `general_id` (`general_id`),
	INDEX `nation_id` (`nation_id`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria
;
##############################
## 국가 테이블
##############################
CREATE TABLE `nation` (
	`nation` INT(6) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_bin',
	`color` CHAR(10) NOT NULL,
	`capital` INT(1) NULL DEFAULT '0',
	`capset` INT(6) NULL DEFAULT '0',
	`gennum` INT(3) NULL DEFAULT '1',
	`gold` INT(8) NULL DEFAULT '0',
	`rice` INT(8) NULL DEFAULT '0',
	`bill` INT(3) NULL DEFAULT '0',
	`rate` INT(3) NULL DEFAULT '0',
	`rate_tmp` INT(3) NULL DEFAULT '0',
	`secretlimit` INT(2) NULL DEFAULT '3',
	`chief_set` INT(11) NOT NULL DEFAULT '0',
	`scout` INT(1) NULL DEFAULT '0',
	`war` INT(1) NULL DEFAULT '0',
	`strategic_cmd_limit` INT(4) NULL DEFAULT '36',
	`surlimit` INT(4) NULL DEFAULT '72',
	`tech` float NULL DEFAULT '0',
	`power` INT(8) NULL DEFAULT '0',
	`spy` TEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`level` INT(1) NULL DEFAULT '0',
	`type` VARCHAR(20) NOT NULL DEFAULT 'che_중립',
	`aux` LONGTEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`nation`),
	CONSTRAINT `json1` CHECK (json_valid(`spy`)),
	CONSTRAINT `json2` CHECK (json_valid(`aux`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
CREATE TABLE `nation_turn` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_id` INT(11) NOT NULL,
	`officer_level` INT(4) NOT NULL,
	`turn_idx` INT(4) NOT NULL,
	`action` VARCHAR(16) NOT NULL COLLATE 'utf8mb4_bin',
	`arg` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`brief` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `nation` (`nation_id`, `officer_level`, `turn_idx`),
	CONSTRAINT `json` CHECK (json_valid(`arg`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 회의실
##############################
CREATE TABLE `board` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_no` INT(11) NOT NULL,
	`is_secret` TINYINT(1) NOT NULL,
	`date` DATETIME NOT NULL,
	`general_no` INT(11) NOT NULL,
	`author` VARCHAR(32) NOT NULL,
	`author_icon` VARCHAR(128) NULL DEFAULT NULL,
	`title` TEXT NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `nation_no` (`nation_no`, `is_secret`, `date`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
CREATE TABLE `comment` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_no` INT(11) NOT NULL,
	`is_secret` TINYINT(1) NOT NULL,
	`date` DATETIME NOT NULL,
	`document_no` INT(11) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`author` VARCHAR(32) NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `nation_no` (`nation_no`, `is_secret`, `date`),
	INDEX `document_no` (`document_no`, `date`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 도시 테이블
##############################
## trade 100 이 표준 시세
CREATE TABLE `city` (
	`city` INT(6) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL,
	`level` INT(1) NOT NULL,
	`nation` INT(6) NOT NULL DEFAULT '0',
	`supply` INT(1) NOT NULL DEFAULT '1',
	`front` INT(1) NOT NULL DEFAULT '0',
	`pop` INT(7) NOT NULL,
	`pop_max` INT(7) NOT NULL,
	`agri` INT(5) NOT NULL,
	`agri_max` INT(5) NOT NULL,
	`comm` INT(5) NOT NULL,
	`comm_max` INT(5) NOT NULL,
	`secu` INT(5) NOT NULL,
	`secu_max` INT(5) NOT NULL,
	`trust` FLOAT NOT NULL,
	`trade` INT(3) NULL DEFAULT NULL,
	`dead` INT(7) NOT NULL DEFAULT '0',
	`def` INT(5) NOT NULL,
	`def_max` INT(5) NOT NULL,
	`wall` INT(5) NOT NULL,
	`wall_max` INT(5) NOT NULL,
	`officer_set` INT(11) NOT NULL DEFAULT '0',
	`state` INT(2) NOT NULL DEFAULT '0',
	`region` INT(2) NOT NULL,
	`term` INT(1) NOT NULL DEFAULT '0',
	`conflict` TEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`city`),
	INDEX `nation` (`nation`),
	CONSTRAINT `json` CHECK (json_valid(`conflict`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 부대 테이블
##############################
CREATE TABLE `troop` (
	`troop_leader` INT(6) NOT NULL,
	`nation` INT(6) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`troop_leader`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
##  락 테이블
##############################
CREATE TABLE `plock` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`type` ENUM('GAME', 'ETC', 'TOURNAMENT') NOT NULL DEFAULT 'GAME',
	`plock` INT(1) NOT NULL DEFAULT '0',
	`locktime` DATETIME(6) NOT NULL,
	PRIMARY KEY (`no`),
	UNIQUE INDEX `type` (`type`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 메시지 테이블
##############################
CREATE TABLE `message` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`mailbox` INT(11) NOT NULL COMMENT '9999 == public, >= 9000 national',
	`type` ENUM('private', 'national', 'public', 'diplomacy') NOT NULL,
	`src` INT(11) NOT NULL,
	`dest` INT(11) NOT NULL,
	`time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`valid_until` DATETIME NOT NULL DEFAULT '9999-12-31 23:59:59',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	INDEX `by_mailbox` (`mailbox`, `type`, `id`),
	CONSTRAINT `json` CHECK (json_valid(`message`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 명전 테이블
##############################
CREATE TABLE IF NOT EXISTS `hall` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`season` INT(11) NOT NULL,
	`scenario` INT(11) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`type` VARCHAR(20) NOT NULL,
	`value` DOUBLE NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`aux` LONGTEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `server_general` (`server_id`, `type`, `general_no`),
	UNIQUE INDEX `owner` (`owner`, `server_id`, `type`),
	INDEX `server_show` (`server_id`, `type`, `value`),
	INDEX `scenario` (`season`, `scenario`, `type`, `value`),
	CONSTRAINT `json` CHECK (json_valid(`aux`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 게임 정보 테이블(유지)
##############################
CREATE TABLE IF NOT EXISTS `ng_games` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`date` DATETIME NOT NULL,
	`winner_nation` INT(11) NULL DEFAULT NULL,
	`map` VARCHAR(50) NULL DEFAULT NULL,
	`season` INT(11) NOT NULL,
	`scenario` INT(11) NOT NULL,
	`scenario_name` TEXT NOT NULL,
	`env` TEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `server_id` (`server_id`),
	INDEX `date` (`date`),
	CONSTRAINT `json` CHECK (json_valid(`env`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 멸망한 국가 목록(유지)
##############################
CREATE TABLE IF NOT EXISTS `ng_old_nations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL DEFAULT '0',
	`nation` INT(11) NOT NULL DEFAULT '0',
	`data` LONGTEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `server_id` (`server_id`, `nation`),
	CONSTRAINT `json` CHECK (json_valid(`data`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 사망한 장수 목록(유지)
##############################
CREATE TABLE IF NOT EXISTS `ng_old_generals` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`name` VARCHAR(32) NOT NULL,
	`last_yearmonth` INT(11) NOT NULL,
	`turntime` DATETIME(6) NOT NULL,
	`data` MEDIUMTEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_no` (`server_id`, `general_no`),
	INDEX `by_name` (`server_id`, `name`),
	INDEX `owner` (`owner`, `server_id`),
	CONSTRAINT `json` CHECK (json_valid(`data`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 왕조 테이블(유지)
##############################
CREATE TABLE IF NOT EXISTS `emperior` (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NULL DEFAULT '',
	`phase` VARCHAR(255) NULL DEFAULT '',
	`nation_count` VARCHAR(64) NULL DEFAULT '',
	`nation_name` TEXT NULL DEFAULT '',
	`nation_hist` TEXT NULL DEFAULT '',
	`gen_count` VARCHAR(64) NULL DEFAULT '',
	`personal_hist` TEXT NULL DEFAULT '',
	`special_hist` TEXT NULL DEFAULT '',
	`name` VARCHAR(64) NULL DEFAULT '',
	`type` VARCHAR(64) NULL DEFAULT '',
	`color` VARCHAR(7) NULL DEFAULT '',
	`year` INT(4) NULL DEFAULT '0',
	`month` INT(2) NULL DEFAULT '0',
	`power` INT(8) NULL DEFAULT '0',
	`gennum` INT(3) NULL DEFAULT '0',
	`citynum` INT(3) NULL DEFAULT '0',
	`pop` VARCHAR(255) NULL DEFAULT '0',
	`poprate` VARCHAR(255) NULL DEFAULT '',
	`gold` INT(9) NULL DEFAULT '0',
	`rice` INT(9) NULL DEFAULT '0',
	`l12name` VARCHAR(64) NULL DEFAULT '',
	`l12pic` VARCHAR(32) NULL DEFAULT '',
	`l11name` VARCHAR(64) NULL DEFAULT '',
	`l11pic` VARCHAR(32) NULL DEFAULT '',
	`l10name` VARCHAR(64) NULL DEFAULT '',
	`l10pic` VARCHAR(32) NULL DEFAULT '',
	`l9name` VARCHAR(64) NULL DEFAULT '',
	`l9pic` VARCHAR(32) NULL DEFAULT '',
	`l8name` VARCHAR(64) NULL DEFAULT '',
	`l8pic` VARCHAR(32) NULL DEFAULT '',
	`l7name` VARCHAR(64) NULL DEFAULT '',
	`l7pic` VARCHAR(32) NULL DEFAULT '',
	`l6name` VARCHAR(64) NULL DEFAULT '',
	`l6pic` VARCHAR(32) NULL DEFAULT '',
	`l5name` VARCHAR(64) NULL DEFAULT '',
	`l5pic` VARCHAR(32) NULL DEFAULT '',
	`tiger` VARCHAR(128) NULL DEFAULT '',
	`eagle` VARCHAR(128) NULL DEFAULT '',
	`gen` TEXT NULL DEFAULT '',
	`history` MEDIUMTEXT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`aux` MEDIUMTEXT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`no`),
	CONSTRAINT `json1` CHECK (json_valid(`history`)),
	CONSTRAINT `json2` CHECK (json_valid(`aux`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 외교 테이블
##############################
create table diplomacy (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`me` INT(6) NOT NULL,
	`you` INT(6) NOT NULL,
	`state` INT(6) NULL DEFAULT '0',
	`term` INT(6) NULL DEFAULT '0',
	`dead` INT(8) NULL DEFAULT '0',
	`showing` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`no`),
	UNIQUE INDEX `me` (`me`, `you`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 외교문서 테이블
##############################
CREATE TABLE `ng_diplomacy` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`src_nation_id` INT(11) NOT NULL,
	`dest_nation_id` INT(11) NOT NULL,
	`prev_no` INT(11) NULL DEFAULT NULL,
	`state` ENUM('proposed', 'activated', 'cancelled', 'replaced') NOT NULL DEFAULT 'proposed',
	`text_brief` TEXT NOT NULL,
	`text_detail` TEXT NOT NULL,
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`src_signer` INT(11) NOT NULL,
	`dest_signer` INT(11) NULL DEFAULT NULL,
	`aux` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`no`),
	INDEX `by_nation_src` (
		`src_nation_id`,
		`dest_nation_id`,
		`state`,
		`date`
	),
	INDEX `by_nation_dest` (
		`dest_nation_id`,
		`src_nation_id`,
		`state`,
		`date`
	),
	CONSTRAINT `json` CHECK (json_valid(`aux`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 토너먼트 테이블
##############################
CREATE TABLE `tournament` (
	`seq` INT(6) NOT NULL AUTO_INCREMENT,
	`no` INT(6) NULL DEFAULT 0,
	`npc` INT(6) NULL DEFAULT 0,
	`name` VARCHAR(64) NULL DEFAULT '',
	`w` VARCHAR(20) NULL DEFAULT 'None',
	`b` VARCHAR(20) NULL DEFAULT 'None',
	`h` VARCHAR(20) NULL DEFAULT 'None',
	`leadership` INT(3) NULL DEFAULT 0,
	`strength` INT(3) NULL DEFAULT 0,
	`intel` INT(3) NULL DEFAULT 0,
	`lvl` INT(3) NULL DEFAULT 0,
	`grp` INT(2) NULL DEFAULT 0,
	`grp_no` INT(2) NULL DEFAULT 0,
	`win` INT(2) NULL DEFAULT 0,
	`draw` INT(2) NULL DEFAULT 0,
	`lose` INT(2) NULL DEFAULT 0,
	`gl` INT(2) NULL DEFAULT 0,
	`prmt` INT(1) NULL DEFAULT 0,
	PRIMARY KEY (`seq`),
	INDEX `grp` (`grp`, `grp_no`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 통계 테이블
##############################
CREATE TABLE `statistic` (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`year` INT(4) NULL DEFAULT '0',
	`month` INT(2) NULL DEFAULT '0',
	`nation_count` INT(2) NULL DEFAULT '0',
	`nation_name` TEXT NULL DEFAULT '',
	`nation_hist` TEXT NULL DEFAULT '',
	`gen_count` VARCHAR(32) NULL DEFAULT '',
	`personal_hist` TEXT NULL DEFAULT '',
	`special_hist` TEXT NULL DEFAULT '',
	`power_hist` TEXT NULL DEFAULT '',
	`crewtype` TEXT NULL DEFAULT '',
	`etc` TEXT NULL DEFAULT '',
	`aux` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`no`),
	CONSTRAINT `json` CHECK (json_valid(`aux`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 연감 테이블
##############################
CREATE TABLE IF NOT EXISTS `ng_history` (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`year` INT(4) NULL,
	`month` INT(2) NULL,
	`map` MEDIUMTEXT NULL DEFAULT NULL,
	`global_history` MEDIUMTEXT NULL DEFAULT NULL,
	`global_action` MEDIUMTEXT NULL DEFAULT NULL,
	`nations` MEDIUMTEXT NULL DEFAULT NULL,
	PRIMARY KEY (`no`),
	INDEX `server_id` (`server_id`, `year`, `month`),
	CONSTRAINT `json1` CHECK (json_valid(`map`)),
	CONSTRAINT `json2` CHECK (json_valid(`global_history`)),
	CONSTRAINT `json3` CHECK (json_valid(`global_action`)),
	CONSTRAINT `json4` CHECK (json_valid(`nations`))
) COLLATE = 'utf8mb4_bin' ENGINE = Aria;
##############################
## 이벤트 핸들러 테이블
##############################
CREATE TABLE `event` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`target` ENUM('MONTH', 'OCCUPY_CITY', 'DESTROY_NATION','PRE_MONTH', 'UNITED') NOT NULL DEFAULT 'MONTH',
	`priority` INT(11) NOT NULL DEFAULT '1000',
	`condition` MEDIUMTEXT NOT NULL COLLATE 'utf8mb4_bin',
	`action` MEDIUMTEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	INDEX `target` (`target`, `priority`, `id`),
	CONSTRAINT `json1` CHECK (json_valid(`condition`)),
	CONSTRAINT `json2` CHECK (json_valid(`action`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 전체 이벤트 기록 테이블
##############################
CREATE TABLE `world_history` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_id` INT(11) NOT NULL,
	`year` INT(4) NOT NULL,
	`month` INT(2) NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`nation_id`, `year`, `month`, `id`),
	INDEX `plain` (`nation_id`, `id`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
############################
## 장수 동향 테이블
##############################
CREATE TABLE `general_record` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`general_id` INT(11) NOT NULL,
	`log_type` ENUM('action', 'battle_brief', 'battle', 'history') NOT NULL,
	`year` INT(4) NOT NULL,
	`month` INT(2) NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`general_id`, `log_type`, `year`, `month`, `id`),
	INDEX `plain` (`general_id`, `log_type`, `id`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 예약 오픈 테이블
##############################
CREATE TABLE IF NOT EXISTS `reserved_open` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`options` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`date`),
	CONSTRAINT `json` CHECK (json_valid(`options`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 장수 선택 토큰
##############################
CREATE TABLE `select_npc_token` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`owner` INT(11) NOT NULL,
	`valid_until` DATETIME NOT NULL,
	`pick_more_from` DATETIME NOT NULL,
	`pick_result` TEXT NOT NULL COLLATE 'utf8mb4_bin',
	`nonce` INT(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `owner` (`owner`),
	INDEX `valid_until` (`valid_until`),
	CONSTRAINT `json` CHECK (json_valid(`pick_result`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 장수 생성 풀 토큰
##############################
CREATE TABLE `select_pool` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`unique_name` VARCHAR(20) NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`general_id` INT(11) NULL DEFAULT NULL,
	`reserved_until` DATETIME NULL DEFAULT NULL,
	`info` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `unique_name` (`unique_name`),
	UNIQUE INDEX `general_id` (`general_id`),
	INDEX `owner` (`owner`),
	INDEX `reserved_until` (`reserved_until`, `general_id`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## KV storage
##############################
CREATE TABLE IF NOT EXISTS `storage` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`namespace` VARCHAR(40) NOT NULL,
	`key` VARCHAR(40) NOT NULL,
	`value` LONGTEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key` (`namespace`, `key`),
	CONSTRAINT `json` CHECK (json_valid(`value`))
) COLLATE = 'utf8mb4_bin' ENGINE = Aria;
CREATE TABLE IF NOT EXISTS `nation_env` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`namespace` INT(11) NOT NULL,
	##storage와 다름!
	`key` VARCHAR(40) NOT NULL,
	`value` LONGTEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key` (`namespace`, `key`),
	CONSTRAINT `json` CHECK (json_valid(`value`))
) COLLATE = 'utf8mb4_bin' ENGINE = Aria;

##############################
## 명장일람
##############################
CREATE TABLE `rank_data` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_id` INT(11) NOT NULL DEFAULT 0,
	`general_id` INT(11) NOT NULL,
	`type` VARCHAR(20) NOT NULL,
	`value` INT(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_general` (`general_id`, `type`),
	INDEX `by_type` (`type`, `value`),
	INDEX `by_nation` (`nation_id`, `type`, `value`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 유저 전용 로그
##############################
CREATE TABLE IF NOT EXISTS `user_record` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`server_id` CHAR(20) NOT NULL,
	`log_type` VARCHAR(20) NOT NULL,
	`year` INT(4) NOT NULL,
	`month` INT(2) NOT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `date1` (
		`user_id`,
		`server_id`,
		`log_type`,
		`year`,
		`month`,
		`id`
	),
	INDEX `date2` (`user_id`, `server_id`, `log_type`, `date`, `id`),
	INDEX `date3` (`server_id`, `date`),
	INDEX `date4` (`server_id`, `year`, `month`, `date`),
	INDEX `plain` (`user_id`, `log_type`, `id`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
##############################
## 신규 베팅
##############################
CREATE TABLE `ng_betting` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`betting_id` INT(11) NOT NULL,
	`general_id` INT(11) NOT NULL,
	`user_id` INT(11) NULL DEFAULT NULL,
	`betting_type` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_bin',
	`amount` INT(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_general` (`general_id`, `betting_id`, `betting_type`),
	UNIQUE INDEX `by_bet` (`betting_id`, `betting_type`, `general_id`),
	INDEX `by_user` (`user_id`, `betting_id`, `betting_type`),
	CONSTRAINT `json` CHECK (json_valid(`betting_type`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;

##############################
## 설문 조사
##############################
CREATE TABLE `vote` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`vote_id` INT(11) NOT NULL,
	`general_id` INT(11) NOT NULL,
	`nation_id` INT(11) NOT NULL,
	`selection` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_general` (`general_id`, `vote_id`),
	INDEX `by_vote` (`vote_id`, `selection`),
	CONSTRAINT `json` CHECK (json_valid(`selection`))
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;
CREATE TABLE `vote_comment` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`vote_id` INT(11) NOT NULL,
	`general_id` INT(11) NOT NULL,
	`nation_id` INT(11) NOT NULL,
	`general_name` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_bin',
	`nation_name` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_bin',
	`text` TEXT NOT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `by_vote` (`vote_id`)
) COLLATE = 'utf8mb4_general_ci' ENGINE = Aria;


##############################
## 거래장 / 경매장
##############################
# 경매 객체는 KVStorage에 저장
CREATE TABLE `ng_auction` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`type` ENUM('buyRice','sellRice','uniqueItem') NOT NULL COLLATE 'utf8mb4_bin',
	`finished` BIT(1) NOT NULL,
	`target` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`host_general_id` INT(11) NOT NULL,
	`req_resource` ENUM('gold','rice','inheritPoint') NOT NULL COLLATE 'utf8mb4_bin',
	`open_date` DATETIME NOT NULL,
	`close_date` DATETIME NOT NULL,
	`detail` LONGTEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `by_close` (`finished`, `type`, `close_date`) USING BTREE,
	INDEX `by_general_id` (`host_general_id`, `type`, `finished`) USING BTREE,
	CONSTRAINT `detail` CHECK (json_valid(`detail`))
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria
;

CREATE TABLE `ng_auction_bid` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`auction_id` INT(11) NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`general_id` INT(11) NOT NULL,
	`amount` INT(11) NOT NULL,
	`date` DATETIME NOT NULL,
	`aux` LONGTEXT NOT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`no`),
	UNIQUE INDEX `by_general` (`general_id`, `auction_id`, `amount`),
	UNIQUE INDEX `by_owner` (`owner`, `auction_id`, `amount`),
	UNIQUE INDEX `by_amount` (`auction_id`, `amount`),
	CONSTRAINT `aux` CHECK (json_valid(`aux`))
)
COLLATE='utf8mb4_general_ci'
ENGINE = Aria;