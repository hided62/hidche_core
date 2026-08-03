<?php

namespace sammo;

/**
 * 게임 진행 시각과 표시용 달력 시각 사이의 유일한 변환 경계입니다.
 *
 * 게임 로직과 DB에는 tick만 저장합니다. 벽시계는 realtime mode의 anchor를
 * 진행시키는 입력으로만 사용하며, manual mode에서는 전혀 참조하지 않습니다.
 */
final class GameClock
{
    public const TICKS_PER_TURN = 36_000_000;
    public const MAX_SAFE_TICK = 9_007_199_254_740_991;
    public const MODE_REALTIME = 'realtime';
    public const MODE_MANUAL = 'manual';

    /** @var null|callable():\DateTimeImmutable */
    private $wallNowProvider;

    public function __construct(
        private readonly \DateTimeImmutable $baseTime,
        private readonly int $turnTermMinutes,
        private readonly int $anchorTick,
        private readonly string $mode,
        private readonly \DateTimeImmutable $wallAnchor,
        ?callable $wallNowProvider = null,
    ) {
        if ($turnTermMinutes <= 0) {
            throw new \InvalidArgumentException('turnterm은 양수여야 합니다.');
        }
        if (self::TICKS_PER_TURN % ($turnTermMinutes * 60) !== 0) {
            throw new \InvalidArgumentException(
                "turnterm {$turnTermMinutes}분은 정수 tick/초로 표현할 수 없습니다."
            );
        }
        if (!in_array($mode, [self::MODE_REALTIME, self::MODE_MANUAL], true)) {
            throw new \InvalidArgumentException("알 수 없는 game clock mode: {$mode}");
        }
        self::requireSafeTick($anchorTick);
        $this->wallNowProvider = $wallNowProvider;
    }

    public static function fromStorage(KVStorage $gameStor, ?callable $wallNowProvider = null): self
    {
        $values = $gameStor->getValues([
            'clock_base_time',
            'clock_tick',
            'clock_mode',
            'clock_wall_anchor',
            'turnterm',
        ]);

        $baseTime = new \DateTimeImmutable((string)$values['clock_base_time']);
        $wallAnchor = new \DateTimeImmutable((string)$values['clock_wall_anchor']);

        return new self(
            $baseTime,
            Util::toInt($values['turnterm']),
            Util::toInt($values['clock_tick']),
            (string)$values['clock_mode'],
            $wallAnchor,
            $wallNowProvider,
        );
    }

    public static function initializeStorage(
        KVStorage $gameStor,
        \DateTimeInterface $baseTime,
        int $turnTermMinutes,
        int $currentTick,
        string $mode = self::MODE_REALTIME,
        ?\DateTimeInterface $wallAnchor = null,
    ): void {
        $wallAnchor ??= self::readWallTime();
        // 생성자 validation을 초기화 경로에도 동일하게 적용합니다.
        new self(
            \DateTimeImmutable::createFromInterface($baseTime),
            $turnTermMinutes,
            $currentTick,
            $mode,
            \DateTimeImmutable::createFromInterface($wallAnchor),
        );

        $gameStor->clock_base_time = TimeUtil::format($baseTime, true);
        $gameStor->clock_tick = $currentTick;
        $gameStor->clock_mode = $mode;
        $gameStor->clock_wall_anchor = TimeUtil::format($wallAnchor, true);
    }

    public function getBaseTime(): \DateTimeImmutable
    {
        return $this->baseTime;
    }

    public function getTurnTermMinutes(): int
    {
        return $this->turnTermMinutes;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function ticksPerSecond(): int
    {
        return intdiv(self::TICKS_PER_TURN, $this->turnTermMinutes * 60);
    }

    public function nowTick(): int
    {
        if ($this->mode === self::MODE_MANUAL) {
            return $this->anchorTick;
        }

        return self::addTicks($this->anchorTick, $this->ticksBetween($this->wallAnchor, $this->wallNow()));
    }

    public function nowDateTime(): \DateTimeImmutable
    {
        return $this->tickToDateTime($this->nowTick());
    }

    public function ticksFromSeconds(int|float $seconds): int
    {
        if (is_int($seconds)) {
            return $seconds * $this->ticksPerSecond();
        }
        return (int)round($seconds * $this->ticksPerSecond());
    }

    public function ticksFromMinutes(int|float $minutes): int
    {
        return $this->ticksFromSeconds($minutes * 60);
    }

    public function addTurns(int $tick, int $turns = 1): int
    {
        if (abs($turns) > intdiv(self::MAX_SAFE_TICK, self::TICKS_PER_TURN)) {
            throw new \OverflowException('turn 수가 JavaScript safe integer tick 범위를 벗어났습니다.');
        }
        return self::addTicks($tick, self::TICKS_PER_TURN * $turns);
    }

    public static function addTicks(int $tick, int $deltaTick): int
    {
        self::requireSafeTick($tick);
        if ($deltaTick > self::MAX_SAFE_TICK || $deltaTick < -self::MAX_SAFE_TICK) {
            throw new \OverflowException('delta tick이 JavaScript safe integer 범위를 벗어났습니다.');
        }
        if (($deltaTick > 0 && $tick > self::MAX_SAFE_TICK - $deltaTick)
            || ($deltaTick < 0 && $tick < -self::MAX_SAFE_TICK - $deltaTick)) {
            throw new \OverflowException('JavaScript safe integer tick 범위를 벗어났습니다.');
        }
        return self::requireSafeTick($tick + $deltaTick);
    }

    public static function requireSafeTick(int $tick): int
    {
        if (abs($tick) > self::MAX_SAFE_TICK) {
            throw new \OverflowException("tick {$tick}은 JavaScript safe integer 범위를 벗어났습니다.");
        }
        return $tick;
    }

    public function floorTurn(int $tick): int
    {
        $remainder = $tick % self::TICKS_PER_TURN;
        if ($remainder < 0) {
            $remainder += self::TICKS_PER_TURN;
        }
        return $tick - $remainder;
    }

    /** @return array{turn:int, subTick:int} */
    public function splitTick(int $tick): array
    {
        $turnStart = $this->floorTurn($tick);
        return [
            'turn' => intdiv($turnStart, self::TICKS_PER_TURN),
            'subTick' => $tick - $turnStart,
        ];
    }

    public function dateTimeToTick(\DateTimeInterface $dateTime): int
    {
        return self::requireSafeTick($this->ticksBetween($this->baseTime, $dateTime));
    }

    public function tickToDateTime(int $tick): \DateTimeImmutable
    {
        self::requireSafeTick($tick);
        $ticksPerSecond = $this->ticksPerSecond();
        $seconds = intdiv($tick, $ticksPerSecond);
        $remainingTicks = $tick % $ticksPerSecond;
        if ($remainingTicks < 0) {
            $seconds--;
            $remainingTicks += $ticksPerSecond;
        }
        $microseconds = intdiv($remainingTicks * 1_000_000, $ticksPerSecond);

        $result = $this->baseTime->modify("{$seconds} seconds");
        if ($microseconds !== 0) {
            $result = $result->modify("{$microseconds} microseconds");
        }
        return $result;
    }

    public function formatTick(int $tick, bool $withFraction = false): string
    {
        return TimeUtil::format($this->tickToDateTime($tick), $withFraction);
    }

    public function formatNow(bool $withFraction = false): string
    {
        return $this->formatTick($this->nowTick(), $withFraction);
    }

    public static function baseTimeForProjection(
        \DateTimeInterface $projectedTime,
        int $tick,
        int $turnTermMinutes,
    ): \DateTimeImmutable {
        $ticksPerSecond = intdiv(self::TICKS_PER_TURN, $turnTermMinutes * 60);
        if ($ticksPerSecond <= 0 || self::TICKS_PER_TURN % ($turnTermMinutes * 60) !== 0) {
            throw new \InvalidArgumentException('정수 tick/초로 표현할 수 없는 turnterm입니다.');
        }
        $seconds = intdiv($tick, $ticksPerSecond);
        $remainingTicks = $tick % $ticksPerSecond;
        if ($remainingTicks < 0) {
            $seconds--;
            $remainingTicks += $ticksPerSecond;
        }
        $microseconds = intdiv($remainingTicks * 1_000_000, $ticksPerSecond);
        $negativeSeconds = -$seconds;
        $result = \DateTimeImmutable::createFromInterface($projectedTime)->modify("{$negativeSeconds} seconds");
        if ($microseconds !== 0) {
            $negativeMicroseconds = -$microseconds;
            $result = $result->modify("{$negativeMicroseconds} microseconds");
        }
        return $result;
    }

    public function persistTick(KVStorage $gameStor, int $tick, ?string $mode = null): void
    {
        self::requireSafeTick($tick);
        $mode ??= $this->mode;
        if (!in_array($mode, [self::MODE_REALTIME, self::MODE_MANUAL], true)) {
            throw new \InvalidArgumentException("알 수 없는 game clock mode: {$mode}");
        }
        $gameStor->clock_tick = $tick;
        $gameStor->clock_mode = $mode;
        // 수동 시계의 전진은 실제 시간에 의존하지 않아야 합니다. realtime으로
        // 전환하는 순간에만 새 벽시계 anchor를 읽습니다.
        $wallAnchor = $mode === self::MODE_REALTIME ? $this->wallNow() : $this->wallAnchor;
        $gameStor->clock_wall_anchor = TimeUtil::format($wallAnchor, true);
    }

    public function advance(KVStorage $gameStor, int $deltaTick): int
    {
        $nextTick = self::addTicks($this->nowTick(), $deltaTick);
        $this->persistTick($gameStor, $nextTick);
        return $nextTick;
    }

    private function ticksBetween(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $secondDiff = $to->getTimestamp() - $from->getTimestamp();
        $microsecondDiff = Util::toInt($to->format('u')) - Util::toInt($from->format('u'));
        $ticksPerSecond = $this->ticksPerSecond();

        return $secondDiff * $ticksPerSecond
            + intdiv($microsecondDiff * $ticksPerSecond, 1_000_000);
    }

    private function wallNow(): \DateTimeImmutable
    {
        if ($this->wallNowProvider !== null) {
            $now = ($this->wallNowProvider)();
            if (!$now instanceof \DateTimeImmutable) {
                throw new \UnexpectedValueException('wallNowProvider는 DateTimeImmutable을 반환해야 합니다.');
            }
            return $now;
        }
        return self::readWallTime();
    }

    /** 운영 anchor와 실행 budget에서만 사용하는 실제 벽시계 입력입니다. */
    public static function readWallTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
