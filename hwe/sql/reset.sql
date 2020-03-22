# 장수 테이블 삭제
DROP TABLE IF EXISTS general;
DROP TABLE IF EXISTS `general_turn`;
# 국가 테이블 삭제
DROP TABLE IF EXISTS nation;
DROP TABLE IF EXISTS `nation_turn`;

# 회의실 테이블 삭제
DROP TABLE IF EXISTS board;
DROP TABLE IF EXISTS comment;

# 회의실 테이블 삭제
DROP TABLE IF EXISTS board;
DROP TABLE IF EXISTS comment;

# 도시 테이블 삭제
DROP TABLE IF EXISTS city;

# 부대 테이블 삭제
DROP TABLE IF EXISTS troop;

# 락 테이블 삭제
DROP TABLE IF EXISTS plock;

# 메시지 테이블 삭제
DROP TABLE IF EXISTS message;

# 명전 테이블은 삭제하지 않음
# 왕조 테이블은 삭제하지 않음

# 외교 테이블 삭제
DROP TABLE IF EXISTS diplomacy;
DROP TABLE IF EXISTS ng_diplomacy;

# 토너먼트 테이블 삭제
DROP TABLE IF EXISTS tournament;

# 거래 테이블 삭제
DROP TABLE IF EXISTS auction;

# 통계 테이블 삭제
DROP TABLE IF EXISTS statistic;

# 이벤트 테이블 삭제
DROP TABLE IF EXISTS event;

# 전체 이벤트 테이블 삭제(연감 대체?)
DROP TABLE IF EXISTS world_history;

# 전체 이벤트 테이블 삭제(연감 대체?)
DROP TABLE IF EXISTS general_public_record;

DROP TABLE IF EXISTS select_npc_token;

DROP TABLE IF EXISTS reserved_open;
CREATE TABLE `reserved_open` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`options` TEXT NULL DEFAULT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `date` (`date`)
)
DEFAULT CHARSET=utf8mb4
ENGINE=Aria;