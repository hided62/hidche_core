-- 기존 테이블 삭제
DROP TABLE IF EXISTS `system`;
DROP TABLE IF EXISTS `member`;
DROP TABLE IF EXISTS `member_log`;

-- 시스템 테이블
-- TODO:장기적으로는 key-value(json) storage 형태로 바꾸는게 나을 듯.
CREATE TABLE `system` (
	`NO` INT(11) NOT NULL AUTO_INCREMENT,
	`REG` VARCHAR(1) NULL DEFAULT 'N',
	`LOGIN` VARCHAR(1) NULL DEFAULT 'N',
	`NOTICE` VARCHAR(256) NULL DEFAULT '',
	`CRT_DATE` DATETIME NULL DEFAULT NULL,
	`MDF_DATE` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`NO`)
)
ENGINE=Aria DEFAULT CHARSET=utf8mb4;

-- 회원 테이블
CREATE TABLE `member` (
	`NO` INT(11) NOT NULL AUTO_INCREMENT,
	`oauth_id` BIGINT(20) NULL DEFAULT NULL,
	`ID` VARCHAR(64) NOT NULL,
	`EMAIL` VARCHAR(64) NULL DEFAULT NULL,
	`oauth_type` ENUM('NONE','KAKAO') NOT NULL,
	`oauth_info` TEXT NOT NULL DEFAULT '{}',
	`token_valid_until` DATETIME NULL DEFAULT NULL,
	`PW` CHAR(128) NOT NULL,
	`salt` CHAR(16) NOT NULL,
	`third_use` INT(1) NOT NULL DEFAULT '0',
	`NAME` VARCHAR(64) NOT NULL,
	`PICTURE` VARCHAR(64) NULL DEFAULT 'default.jpg',
	`IMGSVR` INT(1) NULL DEFAULT '0',
	`acl` TEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`penalty` TEXT NOT NULL DEFAULT '{}' COLLATE 'utf8mb4_bin',
	`GRADE` INT(1) NULL DEFAULT '1',
	`REG_NUM` INT(3) NULL DEFAULT '0',
	`REG_DATE` DATETIME NOT NULL,
	`BLOCK_NUM` INT(3) NULL DEFAULT '0',
	`BLOCK_DATE` DATETIME NULL DEFAULT NULL,
	`delete_after` DATE NULL DEFAULT NULL,
	PRIMARY KEY (`NO`),
	UNIQUE INDEX `ID` (`ID`),
	UNIQUE INDEX `EMAIL` (`EMAIL`),
	UNIQUE INDEX `kauth_id` (`oauth_id`),
	INDEX `delete_after` (`delete_after`),
	CONSTRAINT `json1` CHECK (json_valid(`acl`)),
	CONSTRAINT `json2` CHECK (json_valid(`penalty`))
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria;

-- 로그인 로그 테이블
CREATE TABLE `member_log` (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`member_no` INT(11) NOT NULL,
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`action_type` ENUM('reg','try_login','login','logout','oauth','change_pw','make_general','access_server','delete') NOT NULL,
	`action` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`id`),
	INDEX `action` (`member_no`, `action_type`, `date`),
	INDEX `member` (`member_no`, `date`),
	CONSTRAINT `json` CHECK (json_valid(`action`))
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria;

###################
# KV storage
###################
CREATE TABLE if not exists `storage` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`namespace` VARCHAR(40) NOT NULL,
	`key` VARCHAR(40) NOT NULL,
	`value` TEXT NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `key` (`namespace`, `key`),
	CONSTRAINT `json` CHECK (json_valid(`value`))
)
COLLATE='utf8mb4_bin'
ENGINE=Aria;

CREATE TABLE `login_token` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`base_token` VARCHAR(20) NOT NULL COLLATE 'utf8mb4_general_ci',
	`reg_ip` VARCHAR(40) NOT NULL COLLATE 'utf8mb4_general_ci',
	`reg_date` DATETIME NOT NULL,
	`expire_date` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `by_token` (`base_token`),
	INDEX `by_date` (`user_id`, `expire_date`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=Aria;