# 장수 테이블 삭제
DROP TABLE IF EXISTS general;

# 국가 테이블 삭제
DROP TABLE IF EXISTS nation;

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

# 토너먼트 테이블 삭제
DROP TABLE IF EXISTS tournament;

# 거래 테이블 삭제
DROP TABLE IF EXISTS auction;

# 통계 테이블 삭제
DROP TABLE IF EXISTS statistic;

# 연감 테이블 삭제
DROP TABLE IF EXISTS history;

# 이벤트 테이블 삭제
DROP TABLE IF EXISTS event;

# 전체 이벤트 테이블 삭제(연감 대체?)
DROP TABLE IF EXISTS world_history;

# 전체 이벤트 테이블 삭제(연감 대체?)
DROP TABLE IF EXISTS general_public_record;

DROP TABLE IF EXISTS reserved_open;

DROP TABLE IF EXISTS select_npc_token;