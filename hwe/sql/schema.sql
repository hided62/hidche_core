############################################################################
## 장수 테이블
############################################################################

CREATE TABLE `general` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`owner` INT(11) NOT NULL DEFAULT '0',
	`npcmsg` CHAR(255) NULL DEFAULT '',
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
	`name` CHAR(32) NOT NULL COLLATE 'utf8mb4_bin',
	`name2` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
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
	`dex0` INT(8) NOT NULL DEFAULT '0',
	`dex10` INT(8) NOT NULL DEFAULT '0',
	`dex20` INT(8) NOT NULL DEFAULT '0',
	`dex30` INT(8) NOT NULL DEFAULT '0',
	`dex40` INT(8) NOT NULL DEFAULT '0',
	`level` INT(2) NOT NULL DEFAULT '0',
	`permission` ENUM('normal','auditor','ambassador') NULL DEFAULT 'normal',
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
	`recwar` DATETIME(6) NULL DEFAULT NULL,
	`makelimit` INT(2) NULL DEFAULT '0',
	`killturn` INT(3) NULL DEFAULT NULL,
	`lastconnect` DATETIME NULL DEFAULT NULL,
	`lastrefresh` DATETIME NULL DEFAULT NULL,
	`ip` CHAR(255) NULL DEFAULT '',
	`block` INT(1) NULL DEFAULT '0',
	`dedlevel` INT(2) NULL DEFAULT '0',
	`explevel` INT(2) NULL DEFAULT '0',
	`age` INT(3) NULL DEFAULT '20',
	`startage` INT(3) NULL DEFAULT '20',
	`history` MEDIUMTEXT NULL DEFAULT '',
	`belong` INT(2) NULL DEFAULT '1',
	`betray` INT(2) NULL DEFAULT '0',
	`personal` VARCHAR(20) NOT NULL DEFAULT 'None',
	`special` VARCHAR(20) NOT NULL DEFAULT 'None',
	`specage` INT(2) NULL DEFAULT '0',
	`special2` VARCHAR(20) NOT NULL DEFAULT 'None',
	`specage2` INT(2) NULL DEFAULT '0',
	`defence_train` INT(3) NULL DEFAULT '80',
	`tnmt` INT(1) NULL DEFAULT '1',
	`myset` INT(1) NULL DEFAULT '3',
	`tournament` INT(1) NULL DEFAULT '0',
	`vote` INT(1) NULL DEFAULT '0',
	`newvote` INT(1) NULL DEFAULT '0',
	`last_turn` TEXT NOT NULL DEFAULT '{}',
	`aux` TEXT NOT NULL DEFAULT '{}' COMMENT 'JSON',
	`penalty` TEXT NULL DEFAULT '',
	PRIMARY KEY (`no`),
	INDEX `nation` (`nation`, `npc`),
	INDEX `city` (`city`),
	INDEX `turntime` (`turntime`, `no`),
	INDEX `no_member` (`owner`),
	INDEX `npc` (`npc`),
	INDEX `troop` (`troop`, `turntime`),
	INDEX `level` (`nation`, `level`),
	INDEX `name` (`name`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;

CREATE TABLE `general_turn` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`general_id` INT(11) NOT NULL,
	`turn_idx` INT(4) NOT NULL,
	`action` VARCHAR(16) NOT NULL,
	`arg` TEXT NULL DEFAULT NULL,
	`brief` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `general_id` (`general_id`, `turn_idx`),
	INDEX `action` (`action`, `turn_idx`, `general_id`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria
;

###########################################################################
## 국가 테이블
###########################################################################

CREATE TABLE `nation` (
	`nation` INT(6) NOT NULL AUTO_INCREMENT,
	`name` CHAR(64) NOT NULL COLLATE 'utf8mb4_bin',
	`color` CHAR(10) NOT NULL,
	`can_change_flag` INT(1) NULL DEFAULT '1',
	`onlinegen` VARCHAR(1024) NULL DEFAULT '',
	`msg` TEXT NULL DEFAULT '',
	`capital` INT(1) NULL DEFAULT '0',
	`capset` INT(1) NULL DEFAULT '0',
	`gennum` INT(3) NULL DEFAULT '1',
	`gold` INT(8) NULL DEFAULT '0',
	`rice` INT(8) NULL DEFAULT '0',
	`bill` INT(3) NULL DEFAULT '0',
	`rate` INT(3) NULL DEFAULT '0',
	`rate_tmp` INT(3) NULL DEFAULT '0',
	`secretlimit` INT(2) NULL DEFAULT '3',
	`l12set` INT(1) NULL DEFAULT '0',
	`l11set` INT(1) NULL DEFAULT '0',
	`l10set` INT(1) NULL DEFAULT '0',
	`l9set` INT(1) NULL DEFAULT '0',
	`l8set` INT(1) NULL DEFAULT '0',
	`l7set` INT(1) NULL DEFAULT '0',
	`l6set` INT(1) NULL DEFAULT '0',
	`l5set` INT(1) NULL DEFAULT '0',
	`scout` INT(1) NULL DEFAULT '0',
	`war` INT(1) NULL DEFAULT '0',
	`strategic_cmd_limit` INT(4) NULL DEFAULT '36',
	`surlimit` INT(4) NULL DEFAULT '72',
	`scoutmsg` TEXT NULL DEFAULT '',
	`tech` float NULL DEFAULT '0',
	`power` INT(8) NULL DEFAULT '0',
	`spy` CHAR(255) NOT NULL DEFAULT '{}',
	`level` INT(1) NULL DEFAULT '0',
	`type` VARCHAR(20) NOT NULL DEFAULT 'che_중립',
	`rule` TEXT NULL DEFAULT '',
	`history` MEDIUMTEXT NULL DEFAULT '',
	`aux` TEXT NOT NULL DEFAULT '{}',
	PRIMARY KEY (`nation`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

CREATE TABLE `nation_turn` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`nation_id` INT(11) NOT NULL,
	`level` INT(4) NOT NULL,
	`turn_idx` INT(4) NOT NULL,
	`action` VARCHAR(16) NOT NULL,
	`arg` TEXT NULL DEFAULT NULL,
	`brief` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `nation` (`nation_id`, `level`, `turn_idx`),
	INDEX `action` (`action`, `turn_idx`, `nation_id`, `level`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

##회의실
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
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

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
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

###########################################################################
## 도시 테이블
###########################################################################
## trade 100 이 표준 시세
CREATE TABLE `city` (
	`city` INT(6) NOT NULL AUTO_INCREMENT,
	`name` CHAR(64) NOT NULL,
	`level` INT(1) NOT NULL,
	`upgrading` INT(1) NOT NULL DEFAULT '0',
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
	`officer4` INT(4) NOT NULL DEFAULT '0',
	`officer3` INT(4) NOT NULL DEFAULT '0',
	`officer2` INT(4) NOT NULL DEFAULT '0',
	`officer4set` INT(1) NOT NULL DEFAULT '0',
	`officer3set` INT(1) NOT NULL DEFAULT '0',
	`officer2set` INT(1) NOT NULL DEFAULT '0',
	`state` INT(2) NOT NULL DEFAULT '0',
	`region` INT(2) NOT NULL,
	`term` INT(1) NOT NULL DEFAULT '0',
	`conflict` VARCHAR(500) NOT NULL DEFAULT '{}',
	PRIMARY KEY (`city`),
	INDEX `nation` (`nation`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb4;

###########################################################################
## 부대 테이블
###########################################################################

CREATE TABLE `troop` (
	`troop_leader` INT(6) NOT NULL,
	`nation` INT(6) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`troop_leader`)
) ENGINE=Aria DEFAULT CHARSET=utf8mb4;

##########################################################################
##  락 테이블
##########################################################################

CREATE TABLE `plock` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`plock` INT(1) NOT NULL DEFAULT '0',
	`locktime` DATETIME(6) NOT NULL,
	PRIMARY KEY (`no`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria
;

###########################################################################
## 메시지 테이블
###########################################################################
CREATE TABLE `message` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`mailbox` INT(11) NOT NULL COMMENT '9999 == public, >= 9000 national',
	`type` ENUM('private','national','public','diplomacy') NOT NULL,
	`src` INT(11) NOT NULL,
	`dest` INT(11) NOT NULL,
	`time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`valid_until` DATETIME NOT NULL DEFAULT '9999-12-31 23:59:59',
	`message` TEXT NOT NULL COMMENT 'json',
	PRIMARY KEY (`id`),
	INDEX `by_mailbox` (`mailbox`, `type`, `id`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;

###########################################################################
## 명전 테이블
###########################################################################

CREATE TABLE IF NOT EXISTS `ng_hall` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`season` INT(11) NOT NULL,
	`scenario` INT(11) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`type` INT(11) NOT NULL,
	`value` DOUBLE NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`aux` TEXT NOT NULL DEFAULT '{}',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `server_general` (`server_id`, `type`, `general_no`),
	UNIQUE INDEX `owner` (`owner`, `server_id`, `type`),
	INDEX `server_show` (`server_id`, `type`, `value`),
	INDEX `scenario` (`season`, `scenario`, `type`, `value`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;


###########################################################################
## 왕조 테이블
###########################################################################

CREATE TABLE IF NOT EXISTS `ng_games` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`date` DATETIME NOT NULL,
	`winner_nation` INT(11) NULL DEFAULT NULL,
	`map` VARCHAR(50) NULL DEFAULT NULL,
	`season` INT(11) NOT NULL,
	`scenario` INT(11) NOT NULL,
	`scenario_name` TEXT NOT NULL,
	`env` TEXT NOT NULL COMMENT 'json',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `server_id` (`server_id`),
	INDEX `date` (`date`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ng_old_nations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL DEFAULT '0',
	`nation` INT(11) NOT NULL DEFAULT '0',
	`data` LONGTEXT NOT NULL DEFAULT '0' COMMENT 'json',
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `server_id` (`server_id`, `nation`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

CREATE TABLE IF NOT EXISTS `ng_old_generals` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`name` VARCHAR(32) NOT NULL,
	`last_yearmonth` INT(11) NOT NULL,
	`turntime` DATETIME(6) NOT NULL,
	`data` MEDIUMTEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_no` (`server_id`, `general_no`),
	INDEX `by_name` (`server_id`, `name`),
	INDEX `owner` (`owner`, `server_id`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

CREATE TABLE IF NOT EXISTS `emperior` (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NULL DEFAULT '',
	`phase` CHAR(255) NULL DEFAULT '',
	`nation_count` CHAR(64) NULL DEFAULT '',
	`nation_name` TEXT NULL DEFAULT '',
	`nation_hist` TEXT NULL DEFAULT '',
	`gen_count` CHAR(64) NULL DEFAULT '',
	`personal_hist` TEXT NULL DEFAULT '',
	`special_hist` TEXT NULL DEFAULT '',
	`name` CHAR(64) NULL DEFAULT '',
	`type` CHAR(64) NULL DEFAULT '',
	`color` CHAR(7) NULL DEFAULT '',
	`year` INT(4) NULL DEFAULT '0',
	`month` INT(2) NULL DEFAULT '0',
	`power` INT(8) NULL DEFAULT '0',
	`gennum` INT(3) NULL DEFAULT '0',
	`citynum` INT(3) NULL DEFAULT '0',
	`pop` CHAR(255) NULL DEFAULT '0',
	`poprate` CHAR(255) NULL DEFAULT '',
	`gold` INT(9) NULL DEFAULT '0',
	`rice` INT(9) NULL DEFAULT '0',
	`l12name` CHAR(64) NULL DEFAULT '',
	`l12pic` CHAR(32) NULL DEFAULT '',
	`l11name` CHAR(64) NULL DEFAULT '',
	`l11pic` CHAR(32) NULL DEFAULT '',
	`l10name` CHAR(64) NULL DEFAULT '',
	`l10pic` CHAR(32) NULL DEFAULT '',
	`l9name` CHAR(64) NULL DEFAULT '',
	`l9pic` CHAR(32) NULL DEFAULT '',
	`l8name` CHAR(64) NULL DEFAULT '',
	`l8pic` CHAR(32) NULL DEFAULT '',
	`l7name` CHAR(64) NULL DEFAULT '',
	`l7pic` CHAR(32) NULL DEFAULT '',
	`l6name` CHAR(64) NULL DEFAULT '',
	`l6pic` CHAR(32) NULL DEFAULT '',
	`l5name` CHAR(64) NULL DEFAULT '',
	`l5pic` CHAR(32) NULL DEFAULT '',
	`tiger` VARCHAR(128) NULL DEFAULT '',
	`eagle` VARCHAR(128) NULL DEFAULT '',
	`gen` TEXT NULL DEFAULT '',
	`history` MEDIUMTEXT NULL DEFAULT '',
	`aux` MEDIUMTEXT NULL DEFAULT '' COMMENT 'json',
	PRIMARY KEY (`no`)
) ENGINE=Aria ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4;

###########################################################################
## 외교 테이블
###########################################################################
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
  ) ENGINE=Aria DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ng_diplomacy` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`src_nation_id` INT(11) NOT NULL,
	`dest_nation_id` INT(11) NOT NULL,
	`prev_no` INT(11) NULL DEFAULT NULL,
	`state` ENUM('proposed','activated','cancelled','replaced') NOT NULL DEFAULT 'proposed',
	`text_brief` TEXT NOT NULL,
	`text_detail` TEXT NOT NULL,
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`src_signer` INT(11) NOT NULL,
	`dest_signer` INT(11) NULL DEFAULT NULL,
	`aux` TEXT NULL DEFAULT NULL,
	PRIMARY KEY (`no`),
	INDEX `by_nation_src` (`src_nation_id`, `dest_nation_id`, `state`, `date`),
	INDEX `by_nation_dest` (`dest_nation_id`, `src_nation_id`, `state`, `date`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

###########################################################################
## 토너먼트 테이블
###########################################################################
create table tournament (
  seq  int(6) not null auto_increment,
  no   int(6) default 0,
  npc  int(6) default 0,
  name char(64) default '',
  w    varchar(20) default 'None',
  b    varchar(20) default 'None',
  h    varchar(20) default 'None',
  leadership  int(3) default 0,
  strength  int(3) default 0,
  intel  int(3) default 0,
  lvl  int(3) default 0,
  grp  int(2) default 0,
  grp_no int(2) default 0,
  win  int(2) default 0,
  draw int(2) default 0,
  lose int(2) default 0,
  gl   int(2) default 0,
  prmt int(1) default 0,
  PRIMARY KEY (seq)
  ) ENGINE=Aria DEFAULT CHARSET=utf8mb4;

###########################################################################
## 거래 테이블
###########################################################################
create table auction (
  no     int(6) not null auto_increment,
  type   int(6) default 0,
  no1    int(6) default 0,
  name1  char(64) default '-',
  amount int(6) default 0,
  cost   int(6) default 0,
  value  int(6) default 0,
  topv   int(6) default 0,
  no2    int(6) default 0,
  name2  char(64) default '-',
  expire datetime,

  PRIMARY KEY (no)
  ) ENGINE=Aria DEFAULT CHARSET=utf8mb4;

###########################################################################
## 통계 테이블
###########################################################################
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
	`aux` TEXT NULL DEFAULT '' COMMENT 'json',
	PRIMARY KEY (`no`)
)
ENGINE=Aria ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4;

###########################################################################
## 연감 테이블
###########################################################################
CREATE TABLE IF NOT EXISTS `history` (
	`no` INT(6) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL DEFAULT '',
	`year` INT(4) NULL DEFAULT '0',
	`month` INT(2) NULL DEFAULT '0',
	`map` MEDIUMTEXT NULL DEFAULT '',
	`log` TEXT NULL DEFAULT '',
	`genlog` MEDIUMTEXT NULL DEFAULT '',
	`nation` TEXT NULL DEFAULT '',
	`power` TEXT NULL DEFAULT '',
	`gen` TEXT NULL DEFAULT '',
	`city` TEXT NULL DEFAULT '',
	PRIMARY KEY (`no`),
	INDEX `server_id` (`server_id`, `year`, `month`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

###########################################################################
## 이벤트 핸들러 테이블
###########################################################################

CREATE TABLE `event` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `condition` MEDIUMTEXT NOT NULL COMMENT 'json',
  `action` MEDIUMTEXT NOT NULL COMMENT 'json',
  PRIMARY KEY (`id`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;


##전체 이벤트 기록 테이블
CREATE TABLE `world_history` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`year` INT(4) NOT NULL,
	`month` INT(2) NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`year`, `month`, `id`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria
;

##장수 동향 테이블
CREATE TABLE `general_public_record` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`year` INT(4) NOT NULL,
	`month` INT(2) NOT NULL,
	`text` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`year`, `month`, `id`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria
;

######
# 예약 오픈 테이블
CREATE TABLE IF NOT EXISTS `reserved_open` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`options` TEXT NULL DEFAULT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`date`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;

######
# 장수 선택 토큰
CREATE TABLE `select_npc_token` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`owner` INT(11) NOT NULL,
	`valid_until` DATETIME NOT NULL,
	`pick_more_from` DATETIME NOT NULL,
	`pick_result` TEXT NOT NULL COMMENT 'json',
	`nonce` INT(11) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `owner` (`owner`),
	INDEX `valid_until` (`valid_until`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;

###################
# KV storage
###################
CREATE TABLE IF NOT EXISTS `storage` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`namespace` VARCHAR(40) NOT NULL,
	`key` VARCHAR(40) NOT NULL,
	`value` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key` (`namespace`, `key`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria;

CREATE TABLE IF NOT EXISTS `betting` (
	`general_id` INT(11) NOT NULL,
	`bet0` INT(11) NOT NULL,
	`bet1` INT(11) NOT NULL,
	`bet2` INT(11) NOT NULL,
	`bet3` INT(11) NOT NULL,
	`bet4` INT(11) NOT NULL,
	`bet5` INT(11) NOT NULL,
	`bet6` INT(11) NOT NULL,
	`bet7` INT(11) NOT NULL,
	`bet8` INT(11) NOT NULL,
	`bet9` INT(11) NOT NULL,
	`bet10` INT(11) NOT NULL,
	`bet11` INT(11) NOT NULL,
	`bet12` INT(11) NOT NULL,
	`bet13` INT(11) NOT NULL,
	`bet14` INT(11) NOT NULL,
	`bet15` INT(11) NOT NULL,
	PRIMARY KEY (`general_id`)
)
ENGINE=Aria;

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
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria
;