# 논리 game clock 운영

게임 진행 시각은 `36,000,000 tick × 완료 턴 + 현재 세부 tick`으로 저장합니다.
표시 시각만 `game_env.clock_base_time`을 기준으로 달력 시각으로 투영합니다.

## 기존 DB migration

웹과 턴 daemon을 먼저 중지하고 HWE DB SQL dump를 만든 다음 상태를 확인합니다.

```bash
php scripts/migrate-game-clock.php --status
php scripts/migrate-game-clock.php --apply --backup=/absolute/path/to/hwe-before-clock.sql
```

적용 명령은 비어 있지 않은 절대경로 backup과 `GAME` lock을 요구합니다. Aria
DDL은 transaction rollback이 되지 않으므로 기존 날짜 컬럼과 `game_env` 값은
`*_wall_backup`으로 남깁니다. 새 코드 검증이 끝나기 전에는 이 값을 제거하지
마세요. 복구할 때는 PHP/daemon을 중지하고 명령에 지정했던 전체 SQL dump를
복원하는 것이 기준 절차입니다.

## 시계 조회와 전진

```bash
php scripts/game-clock.php --status
php scripts/game-clock.php --advance-turns=12 --apply
php scripts/game-clock.php --advance-ticks=36000000 --apply
php scripts/game-clock.php --mode=realtime --apply
```

명시적으로 전진하면 시계는 `manual` 모드가 됩니다. 이 모드의 엔진은 실제
시각을 읽지 않습니다. `realtime`으로 전환할 때 현재 논리 tick을 새 벽시계
anchor에 고정하므로 표시 시각이 튀지 않습니다.

격리 DB에서 manual clock과 엔진 진행을 함께 확인할 수 있습니다.

```bash
php scripts/verify-game-clock-engine.php --apply --engine-calls=2
```

이 검증기는 manual mode만 허용하고, 엔진 호출 전후 clock tick이 벽시계 때문에
변하지 않았는지와 마지막 처리 tick이 현재 tick을 넘지 않았는지 검사합니다.
