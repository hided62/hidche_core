############################################################################
## 장수 테이블
############################################################################

CREATE TABLE `general` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`owner` INT(11) NOT NULL DEFAULT '0',
	`npcmsg` CHAR(255) NULL DEFAULT '',
	`npcid` INT(5) NULL DEFAULT NULL,
	`npc` INT(1) NULL DEFAULT '0',
	`npc_org` INT(1) NULL DEFAULT '0',
	`affinity` INT(3) NULL DEFAULT '0',
	`bornyear` INT(3) NULL DEFAULT '180',
	`deadyear` INT(3) NULL DEFAULT '300',
	`newmsg` INT(1) NULL DEFAULT '0',
	`con` INT(6) NULL DEFAULT '0',
	`connect` INT(6) NULL DEFAULT '0',
	`refresh` INT(6) NULL DEFAULT '0',
	`logcnt` INT(6) NULL DEFAULT '1',
	`refcnt` INT(6) NULL DEFAULT '1',
	`picture` VARCHAR(40) NOT NULL,
	`imgsvr` INT(1) NULL DEFAULT '0',
	`name` CHAR(32) NOT NULL COLLATE 'utf8mb4_bin',
	`name2` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`nation` INT(6) NULL DEFAULT '0',
	`nations` VARCHAR(64) NOT NULL DEFAULT '[0]',
	`city` INT(6) NULL DEFAULT '3',
	`troop` INT(6) NULL DEFAULT '0',
	`leader` INT(3) NULL DEFAULT '50',
	`leader2` INT(3) NULL DEFAULT '0',
	`power` INT(3) NULL DEFAULT '50',
	`power2` INT(3) NULL DEFAULT '0',
	`intel` INT(3) NULL DEFAULT '50',
	`intel2` INT(3) NULL DEFAULT '0',
	`injury` INT(2) NULL DEFAULT '0',
	`experience` INT(6) NULL DEFAULT '0',
	`dedication` INT(6) NULL DEFAULT '0',
	`dex0` INT(8) NULL DEFAULT '0',
	`dex10` INT(8) NULL DEFAULT '0',
	`dex20` INT(8) NULL DEFAULT '0',
	`dex30` INT(8) NULL DEFAULT '0',
	`dex40` INT(8) NULL DEFAULT '0',
	`level` INT(2) NULL DEFAULT '0',
	`gold` INT(6) NULL DEFAULT '1000',
	`rice` INT(6) NULL DEFAULT '1000',
	`crew` INT(5) NULL DEFAULT '0',
	`crewtype` INT(2) NULL DEFAULT '0',
	`train` INT(3) NULL DEFAULT '0',
	`atmos` INT(3) NULL DEFAULT '0',
	`weap` INT(2) NULL DEFAULT '0',
	`book` INT(2) NULL DEFAULT '0',
	`horse` INT(2) NULL DEFAULT '0',
	`item` INT(2) NULL DEFAULT '0',
	`turntime` DATETIME NULL DEFAULT NULL,
	`recwar` DATETIME NULL DEFAULT NULL,
	`makenation` CHAR(255) NULL DEFAULT NULL,
	`makelimit` INT(2) NULL DEFAULT '0',
	`killturn` INT(3) NULL DEFAULT NULL,
	`lastconnect` DATETIME NULL DEFAULT NULL,
	`lastrefresh` DATETIME NULL DEFAULT NULL,
	`ip` CHAR(255) NULL DEFAULT '',
	`block` INT(1) NULL DEFAULT '0',
	`dedlevel` INT(2) NULL DEFAULT '0',
	`explevel` INT(2) NULL DEFAULT '0',
	`firenum` INT(3) NULL DEFAULT '0',
	`warnum` INT(3) NULL DEFAULT '0',
	`killnum` INT(3) NULL DEFAULT '0',
	`deathnum` INT(3) NULL DEFAULT '0',
	`killcrew` INT(7) NULL DEFAULT '0',
	`deathcrew` INT(7) NULL DEFAULT '0',
	`age` INT(3) NULL DEFAULT '20',
	`startage` INT(3) NULL DEFAULT '20',
	`history` MEDIUMTEXT NULL DEFAULT '',
	`belong` INT(2) NULL DEFAULT '1',
	`betray` INT(2) NULL DEFAULT '0',
	`personal` INT(2) NULL DEFAULT '0',
	`special` INT(2) NULL DEFAULT '0',
	`specage` INT(2) NULL DEFAULT '0',
	`special2` INT(2) NULL DEFAULT '0',
	`specage2` INT(2) NULL DEFAULT '0',
	`mode` INT(1) NULL DEFAULT '2',
	`tnmt` INT(1) NULL DEFAULT '1',
	`myset` INT(1) NULL DEFAULT '3',
	`tournament` INT(1) NULL DEFAULT '0',
	`vote` INT(1) NULL DEFAULT '0',
	`newvote` INT(1) NULL DEFAULT '0',
	`ttw` INT(4) NULL DEFAULT '0',
	`ttd` INT(4) NULL DEFAULT '0',
	`ttl` INT(4) NULL DEFAULT '0',
	`ttg` INT(4) NULL DEFAULT '0',
	`ttp` INT(4) NULL DEFAULT '0',
	`tlw` INT(4) NULL DEFAULT '0',
	`tld` INT(4) NULL DEFAULT '0',
	`tll` INT(4) NULL DEFAULT '0',
	`tlg` INT(4) NULL DEFAULT '0',
	`tlp` INT(4) NULL DEFAULT '0',
	`tpw` INT(4) NULL DEFAULT '0',
	`tpd` INT(4) NULL DEFAULT '0',
	`tpl` INT(4) NULL DEFAULT '0',
	`tpg` INT(4) NULL DEFAULT '0',
	`tpp` INT(4) NULL DEFAULT '0',
	`tiw` INT(4) NULL DEFAULT '0',
	`tid` INT(4) NULL DEFAULT '0',
	`til` INT(4) NULL DEFAULT '0',
	`tig` INT(4) NULL DEFAULT '0',
	`tip` INT(4) NULL DEFAULT '0',
	`bet0` INT(8) NULL DEFAULT '0',
	`bet1` INT(8) NULL DEFAULT '0',
	`bet2` INT(8) NULL DEFAULT '0',
	`bet3` INT(8) NULL DEFAULT '0',
	`bet4` INT(8) NULL DEFAULT '0',
	`bet5` INT(8) NULL DEFAULT '0',
	`bet6` INT(8) NULL DEFAULT '0',
	`bet7` INT(8) NULL DEFAULT '0',
	`bet8` INT(8) NULL DEFAULT '0',
	`bet9` INT(8) NULL DEFAULT '0',
	`bet10` INT(8) NULL DEFAULT '0',
	`bet11` INT(8) NULL DEFAULT '0',
	`bet12` INT(8) NULL DEFAULT '0',
	`bet13` INT(8) NULL DEFAULT '0',
	`bet14` INT(8) NULL DEFAULT '0',
	`bet15` INT(8) NULL DEFAULT '0',
	`betwin` INT(8) NULL DEFAULT '0',
	`betgold` INT(8) NULL DEFAULT '0',
	`betwingold` INT(8) NULL DEFAULT '0',
	`term` INT(4) NULL DEFAULT '0',
	`turn0` CHAR(14) NULL DEFAULT '00000000000000',
	`turn1` CHAR(14) NULL DEFAULT '00000000000000',
	`turn2` CHAR(14) NULL DEFAULT '00000000000000',
	`turn3` CHAR(14) NULL DEFAULT '00000000000000',
	`turn4` CHAR(14) NULL DEFAULT '00000000000000',
	`turn5` CHAR(14) NULL DEFAULT '00000000000000',
	`turn6` CHAR(14) NULL DEFAULT '00000000000000',
	`turn7` CHAR(14) NULL DEFAULT '00000000000000',
	`turn8` CHAR(14) NULL DEFAULT '00000000000000',
	`turn9` CHAR(14) NULL DEFAULT '00000000000000',
	`turn10` CHAR(14) NULL DEFAULT '00000000000000',
	`turn11` CHAR(14) NULL DEFAULT '00000000000000',
	`turn12` CHAR(14) NULL DEFAULT '00000000000000',
	`turn13` CHAR(14) NULL DEFAULT '00000000000000',
	`turn14` CHAR(14) NULL DEFAULT '00000000000000',
	`turn15` CHAR(14) NULL DEFAULT '00000000000000',
	`turn16` CHAR(14) NULL DEFAULT '00000000000000',
	`turn17` CHAR(14) NULL DEFAULT '00000000000000',
	`turn18` CHAR(14) NULL DEFAULT '00000000000000',
	`turn19` CHAR(14) NULL DEFAULT '00000000000000',
	`turn20` CHAR(14) NULL DEFAULT '00000000000000',
	`turn21` CHAR(14) NULL DEFAULT '00000000000000',
	`turn22` CHAR(14) NULL DEFAULT '00000000000000',
	`turn23` CHAR(14) NULL DEFAULT '00000000000000',
	`recturn` CHAR(14) NULL DEFAULT '',
	`resturn` CHAR(14) NULL DEFAULT '',
	PRIMARY KEY (`no`),
	INDEX `nation` (`nation`, `npc`),
	INDEX `city` (`city`),
	INDEX `turntime` (`turntime`, `no`),
	INDEX `no_member` (`owner`),
	INDEX `npc` (`npc`),
	INDEX `npcid` (`npcid`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=MyISAM;

###########################################################################
## 국가 테이블
###########################################################################

create table nation (
  nation  int(6) not null auto_increment,
  `name` CHAR(64) NOT NULL COLLATE 'utf8mb4_bin',
  color   char(10) not null,  can_change_flag int(1) default 1,
  onlinegen   varchar(1024) default '',
  msg     text default '',
  capital int(1) default 0,   capset int(1) default 0,
  gennum  int(3) default 1,
  gennum2 int(3) default 1,
  chemi   int(3) default 0,
  gold    int(8) default 0,
  rice    int(8) default 0,
  bill    int(3) default 0,
  rate    int(3) default 0,
  rate_tmp int(3) default 0,
  secretlimit int(2) default 3,
  l12set  int(1) default 0,
  l11set  int(1) default 0,
  l10set  int(1) default 0,
  l9set int(1) default 0,
  l8set int(1) default 0,
  l7set int(1) default 0,
  l6set int(1) default 0,
  l5set int(1) default 0,
  scout int(1) default 0,
  war     int(1) default 0,
  sabotagelimit int(4) default 36,
  surlimit int(4) default 72,
  scoutmsg text default '',
  tech int(8) default 0,  totaltech int(8) default 0,
  power int(8) default 0,
  spy  char(255) default '',
  level int(1) default 0,
  type int(2) default 0,
  rule text default '',
  history mediumtext default '',
  board0  text default '', board0_who  int(6) default 0, board0_when  datetime,
  board1  text default '', board1_who  int(6) default 0, board1_when  datetime,
  board2  text default '', board2_who  int(6) default 0, board2_when  datetime,
  board3  text default '', board3_who  int(6) default 0, board3_when  datetime,
  board4  text default '', board4_who  int(6) default 0, board4_when  datetime,
  board5  text default '', board5_who  int(6) default 0, board5_when  datetime,
  board6  text default '', board6_who  int(6) default 0, board6_when  datetime,
  board7  text default '', board7_who  int(6) default 0, board7_when  datetime,
  board8  text default '', board8_who  int(6) default 0, board8_when  datetime,
  board9  text default '', board9_who  int(6) default 0, board9_when  datetime,
  board10 text default '', board10_who int(6) default 0, board10_when datetime,
  board11 text default '', board11_who int(6) default 0, board11_when datetime,
  board12 text default '', board12_who int(6) default 0, board12_when datetime,
  board13 text default '', board13_who int(6) default 0, board13_when datetime,
  board14 text default '', board14_who int(6) default 0, board14_when datetime,
  board15 text default '', board15_who int(6) default 0, board15_when datetime,
  board16 text default '', board16_who int(6) default 0, board16_when datetime,
  board17 text default '', board17_who int(6) default 0, board17_when datetime,
  board18 text default '', board18_who int(6) default 0, board18_when datetime,
  board19 text default '', board19_who int(6) default 0, board19_when datetime,
  coreboard0  text default '', coreboard0_who  int(6) default 0, coreboard0_when  datetime,
  coreboard1  text default '', coreboard1_who  int(6) default 0, coreboard1_when  datetime,
  coreboard2  text default '', coreboard2_who  int(6) default 0, coreboard2_when  datetime,
  coreboard3  text default '', coreboard3_who  int(6) default 0, coreboard3_when  datetime,
  coreboard4  text default '', coreboard4_who  int(6) default 0, coreboard4_when  datetime,
  coreboard5  text default '', coreboard5_who  int(6) default 0, coreboard5_when  datetime,
  coreboard6  text default '', coreboard6_who  int(6) default 0, coreboard6_when  datetime,
  coreboard7  text default '', coreboard7_who  int(6) default 0, coreboard7_when  datetime,
  coreboard8  text default '', coreboard8_who  int(6) default 0, coreboard8_when  datetime,
  coreboard9  text default '', coreboard9_who  int(6) default 0, coreboard9_when  datetime,
  coreboard10 text default '', coreboard10_who int(6) default 0, coreboard10_when datetime,
  coreboard11 text default '', coreboard11_who int(6) default 0, coreboard11_when datetime,
  coreboard12 text default '', coreboard12_who int(6) default 0, coreboard12_when datetime,
  coreboard13 text default '', coreboard13_who int(6) default 0, coreboard13_when datetime,
  coreboard14 text default '', coreboard14_who int(6) default 0, coreboard14_when datetime,
  coreboard15 text default '', coreboard15_who int(6) default 0, coreboard15_when datetime,
  coreboard16 text default '', coreboard16_who int(6) default 0, coreboard16_when datetime,
  coreboard17 text default '', coreboard17_who int(6) default 0, coreboard17_when datetime,
  coreboard18 text default '', coreboard18_who int(6) default 0, coreboard18_when datetime,
  coreboard19 text default '', coreboard19_who int(6) default 0, coreboard19_when datetime,
  boardindex int(2) default 19,
  coreindex int(2) default 19,
  l12term   int(4)   default 0,                l11term   int(4)   default 0,                l10term   int(4)   default 0,                l9term   int(4)   default 0,
  l12turn0  char(14) default '00000000000099', l11turn0  char(14) default '00000000000099', l10turn0  char(14) default '00000000000099', l9turn0  char(14) default '00000000000099',
  l12turn1  char(14) default '00000000000099', l11turn1  char(14) default '00000000000099', l10turn1  char(14) default '00000000000099', l9turn1  char(14) default '00000000000099',
  l12turn2  char(14) default '00000000000099', l11turn2  char(14) default '00000000000099', l10turn2  char(14) default '00000000000099', l9turn2  char(14) default '00000000000099',
  l12turn3  char(14) default '00000000000099', l11turn3  char(14) default '00000000000099', l10turn3  char(14) default '00000000000099', l9turn3  char(14) default '00000000000099',
  l12turn4  char(14) default '00000000000099', l11turn4  char(14) default '00000000000099', l10turn4  char(14) default '00000000000099', l9turn4  char(14) default '00000000000099',
  l12turn5  char(14) default '00000000000099', l11turn5  char(14) default '00000000000099', l10turn5  char(14) default '00000000000099', l9turn5  char(14) default '00000000000099',
  l12turn6  char(14) default '00000000000099', l11turn6  char(14) default '00000000000099', l10turn6  char(14) default '00000000000099', l9turn6  char(14) default '00000000000099',
  l12turn7  char(14) default '00000000000099', l11turn7  char(14) default '00000000000099', l10turn7  char(14) default '00000000000099', l9turn7  char(14) default '00000000000099',
  l12turn8  char(14) default '00000000000099', l11turn8  char(14) default '00000000000099', l10turn8  char(14) default '00000000000099', l9turn8  char(14) default '00000000000099',
  l12turn9  char(14) default '00000000000099', l11turn9  char(14) default '00000000000099', l10turn9  char(14) default '00000000000099', l9turn9  char(14) default '00000000000099',
  l12turn10 char(14) default '00000000000099', l11turn10 char(14) default '00000000000099', l10turn10 char(14) default '00000000000099', l9turn10 char(14) default '00000000000099',
  l12turn11 char(14) default '00000000000099', l11turn11 char(14) default '00000000000099', l10turn11 char(14) default '00000000000099', l9turn11 char(14) default '00000000000099',

  l8term  int(4)   default 0,                 l7term   int(4)   default 0,                l6term   int(4)   default 0,                l5term   int(4)   default 0,
  l8turn0  char(14) default '00000000000099', l7turn0  char(14) default '00000000000099', l6turn0  char(14) default '00000000000099', l5turn0  char(14) default '00000000000099',
  l8turn1  char(14) default '00000000000099', l7turn1  char(14) default '00000000000099', l6turn1  char(14) default '00000000000099', l5turn1  char(14) default '00000000000099',
  l8turn2  char(14) default '00000000000099', l7turn2  char(14) default '00000000000099', l6turn2  char(14) default '00000000000099', l5turn2  char(14) default '00000000000099',
  l8turn3  char(14) default '00000000000099', l7turn3  char(14) default '00000000000099', l6turn3  char(14) default '00000000000099', l5turn3  char(14) default '00000000000099',
  l8turn4  char(14) default '00000000000099', l7turn4  char(14) default '00000000000099', l6turn4  char(14) default '00000000000099', l5turn4  char(14) default '00000000000099',
  l8turn5  char(14) default '00000000000099', l7turn5  char(14) default '00000000000099', l6turn5  char(14) default '00000000000099', l5turn5  char(14) default '00000000000099',
  l8turn6  char(14) default '00000000000099', l7turn6  char(14) default '00000000000099', l6turn6  char(14) default '00000000000099', l5turn6  char(14) default '00000000000099',
  l8turn7  char(14) default '00000000000099', l7turn7  char(14) default '00000000000099', l6turn7  char(14) default '00000000000099', l5turn7  char(14) default '00000000000099',
  l8turn8  char(14) default '00000000000099', l7turn8  char(14) default '00000000000099', l6turn8  char(14) default '00000000000099', l5turn8  char(14) default '00000000000099',
  l8turn9  char(14) default '00000000000099', l7turn9  char(14) default '00000000000099', l6turn9  char(14) default '00000000000099', l5turn9  char(14) default '00000000000099',
  l8turn10 char(14) default '00000000000099', l7turn10 char(14) default '00000000000099', l6turn10 char(14) default '00000000000099', l5turn10 char(14) default '00000000000099',
  l8turn11 char(14) default '00000000000099', l7turn11 char(14) default '00000000000099', l6turn11 char(14) default '00000000000099', l5turn11 char(14) default '00000000000099',

  PRIMARY KEY (nation)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

###########################################################################
## 도시 테이블
###########################################################################
## trade 100 이 표준 시세
create table city (
  `city` INT(6) NOT NULL AUTO_INCREMENT,
	`name` CHAR(64) NOT NULL,
  level  int(1) default 0,
  upgrading int(1) default 0,
  nation int(6) default 0,
  supply int(1) default 1,
  path   char(32) default '',
  front  int(1) default 0,
  pop    int(7) default 50000,
  pop2   int(7) default 50000,
  agri   int(5) default 0,
  agri2  int(5) default 0,
  comm   int(5) default 0,
  comm2  int(5) default 0,
  secu   int(5) default 0,
  secu2  int(5) default 0,
  rate   int(3) default 0,
  trade  int(3) default 100,
  dead   int(7) default 0,
  def    int(5) default 0,
  def2   int(5) default 0,
  wall   int(5) default 0,
  wall2  int(5) default 0,
  gen1   int(4) default 0,
  gen2   int(4) default 0,
  gen3   int(4) default 0,
  gen1set int(1) default 0,
  gen2set int(1) default 0,
  gen3set int(1) default 0,
  state   int(2) default 0,
  region  int(2) default 0,
  term    int(1) default 0,
  conflict    varchar(500) default '{}',

  PRIMARY KEY (city),
  KEY (nation)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

###########################################################################
## 부대 테이블
###########################################################################

create table troop (
  troop int(6) not null auto_increment,
  name char(64) not null,
  nation int(6) not null,
  no int(6) not null,

  PRIMARY KEY (troop)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

##########################################################################
##  락 테이블
##########################################################################

create table plock (
  no          int(11) not null auto_increment,
  plock       int(1) default 0,

  PRIMARY KEY (no)
  ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

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
ENGINE=InnoDB;

###########################################################################
## 명전 테이블
###########################################################################

CREATE TABLE IF NOT EXISTS `ng_hall` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
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
	INDEX `scenario` (`scenario`, `type`, `value`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;


###########################################################################
## 왕조 테이블
###########################################################################

CREATE TABLE IF NOT EXISTS `ng_games` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`date` DATETIME NOT NULL,
	`winner_nation` INT(11) NULL DEFAULT NULL,
	`scenario` INT(11) NOT NULL,
	`scenario_name` TEXT NOT NULL,
	`env` TEXT NOT NULL COMMENT 'json',
	PRIMARY KEY (`id`),
	UNIQUE INDEX `server_id` (`server_id`),
	INDEX `date` (`date`)
)
ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ng_old_nations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL DEFAULT '0',
	`nation` INT(11) NOT NULL DEFAULT '0',
	`data` LONGTEXT NOT NULL DEFAULT '0' COMMENT 'json',
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	INDEX `server_id` (`server_id`, `nation`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

CREATE TABLE IF NOT EXISTS `ng_old_generals` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`server_id` CHAR(20) NOT NULL,
	`general_no` INT(11) NOT NULL,
	`owner` INT(11) NULL DEFAULT NULL,
	`name` VARCHAR(32) NOT NULL,
	`last_yearmonth` INT(11) NOT NULL,
	`turntime` DATETIME NOT NULL,
	`data` MEDIUMTEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_no` (`server_id`, `general_no`),
	INDEX `by_name` (`server_id`, `name`),
	INDEX `owner` (`owner`, `server_id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

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
	`tiger` CHAR(64) NULL DEFAULT '',
	`eagle` CHAR(64) NULL DEFAULT '',
	`gen` TEXT NULL DEFAULT '',
	`history` MEDIUMTEXT NULL DEFAULT '',
	`aux` MEDIUMTEXT NULL DEFAULT '' COMMENT 'json',
	PRIMARY KEY (`no`)
) ENGINE=INNODB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4;

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
  `fixed` CHAR(128) NULL DEFAULT '',
  `reserved` CHAR(128) NULL DEFAULT '',
  `showing` DATETIME NULL DEFAULT NULL,

  PRIMARY KEY (`no`),
  UNIQUE INDEX `me` (`me`, `you`)
  ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

###########################################################################
## 토너먼트 테이블
###########################################################################
create table tournament (
  seq  int(6) not null auto_increment,
  no   int(6) default 0,
  npc  int(6) default 0,
  name char(64) default '',
  w    int(2) default 0,
  b    int(2) default 0,
  h    int(2) default 0,
  ldr  int(3) default 0,
  pwr  int(3) default 0,
  itl  int(3) default 0,
  lvl  int(3) default 0,
  grp  int(2) default 0,
  grp_no int(2) default 0,
  win  int(2) default 0,
  draw int(2) default 0,
  lose int(2) default 0,
  gl   int(2) default 0,
  prmt int(1) default 0,
  PRIMARY KEY (seq)
  ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

###########################################################################
## 거래 테이블
###########################################################################
create table auction (
  no     int(6) not null auto_increment,
  type   int(6) default 0,
  no1    int(6) default 0,
  name1  char(64) default '-',
  stuff  int(6) default 0,
  amount int(6) default 0,
  cost   int(6) default 0,
  value  int(6) default 0,
  topv   int(6) default 0,
  no2    int(6) default 0,
  name2  char(64) default '-',
  expire datetime,

  PRIMARY KEY (no)
  ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

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
ENGINE=INNODB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4;

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
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPRESSED;

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
ENGINE=InnoDB;


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
ENGINE=InnoDB
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
ENGINE=InnoDB
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
ENGINE=MyISAM;

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
ENGINE=MyISAM;

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
ENGINE=MyISAM
