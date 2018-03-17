-- 기존 테이블 삭제
DROP TABLE IF EXISTS `system`;
DROP TABLE IF EXISTS `member`;
DROP TABLE IF EXISTS `member_log`;
DROP TABLE IF EXISTS `auth_kakao`;

-- 시스템 테이블

CREATE TABLE `system` (
	`NO` INT(11) NOT NULL AUTO_INCREMENT,
	`REG` VARCHAR(1) NULL DEFAULT 'N',
	`LOGIN` VARCHAR(1) NULL DEFAULT 'N',
	`NOTICE` VARCHAR(256) NULL DEFAULT '',
	`CRT_DATE` DATETIME NULL DEFAULT NULL,
	`MDF_DATE` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`NO`)
)
ENGINE=InnoDB DEFAULT CHARSET=UTF8;

-- 회원 테이블
CREATE TABLE `member` (
	`NO` INT(11) NOT NULL AUTO_INCREMENT,
	`oauth_id` BIGINT(20) NULL DEFAULT NULL,
	`oauth_type` ENUM('NONE','KAKAO') NULL DEFAULT NULL,
	`ID` VARCHAR(64) NOT NULL,
	`EMAIL` VARCHAR(64) NULL DEFAULT NULL,
	`PW` CHAR(128) NOT NULL,
	`salt` CHAR(16) NOT NULL,
	`NAME` VARCHAR(64) NOT NULL,
	`PICTURE` VARCHAR(64) NULL DEFAULT 'default.jpg',
	`IMGSVR` INT(1) NULL DEFAULT '0',
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
	INDEX `delete_after` (`delete_after`)
)
ENGINE=InnoDB DEFAULT CHARSET=UTF8;


-- 인증 테이블
CREATE TABLE `auth_kakao` (
	`no` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`id` BIGINT(20) NOT NULL COMMENT 'after signup',
	`access_token` CHAR(128) NOT NULL COMMENT 'after token',
	`refresh_token` CHAR(128) NOT NULL,
	`expires` DATETIME NOT NULL,
	`refresh_token_expires` DATETIME NOT NULL,
	`datetime` DATETIME NOT NULL,
	`email` VARCHAR(128) NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `access_token` (`access_token`),
	INDEX `id` (`id`),
	INDEX `expires` (`expires`),
	INDEX `email` (`email`),
	INDEX `refresh_expires` (`refresh_token_expires`),
	INDEX `datetime` (`datetime`)
)
ENGINE=InnoDB DEFAULT CHARSET=UTF8;

-- 로그인 로그 테이블
CREATE TABLE `member_log` (
	`id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`member_no` INT(11) NOT NULL,
	`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`action_type` ENUM('reg','try_login','login','logout','oauth','make_general','access_server') NOT NULL,
	`action` TEXT NULL DEFAULT NULL COMMENT 'JSON',
	PRIMARY KEY (`id`),
	INDEX `action` (`member_no`, `action_type`, `date`),
	INDEX `member` (`member_no`, `date`)
)
ENGINE=MyISAM DEFAULT CHARSET=UTF8;