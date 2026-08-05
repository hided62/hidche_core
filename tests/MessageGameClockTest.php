<?php

namespace sammo;

use PHPUnit\Framework\TestCase;
use sammo\Enums\MessageType;

require_once __DIR__ . '/../hwe/sammo/Target.php';
require_once __DIR__ . '/../hwe/sammo/MessageTarget.php';
require_once __DIR__ . '/../hwe/sammo/Enums/MessageType.php';
require_once __DIR__ . '/../hwe/sammo/Message.php';

final class MessageGameClockTest extends TestCase
{
    public function testUnlimitedMessageReusesExactTicksAcrossReceiverAndSenderCopies(): void
    {
        $base = new \DateTimeImmutable('2026-08-04 00:00:00.000000');
        $wallTimes = [
            new \DateTimeImmutable('2026-08-04 00:00:00.000000'),
            new \DateTimeImmutable('2026-08-04 00:00:00.938140'),
        ];
        $wallReads = 0;
        $clock = new GameClock(
            $base,
            1,
            0,
            GameClock::MODE_REALTIME,
            $base,
            static function () use (&$wallTimes, &$wallReads): \DateTimeImmutable {
                return $wallTimes[$wallReads++];
            },
        );
        $message = new TestableClockMessage(
            MessageType::national,
            $this->createMock(MessageTarget::class),
            $this->createMock(MessageTarget::class),
            '선전포고',
            new \DateTime('2026-08-04 00:00:00.000000'),
            new \DateTime('9999-12-31'),
            [],
        );

        $receiverTicks = $message->resolveTicksForTest($clock);
        self::assertLessThan(9000, Util::toInt($message->validUntil->format('Y')));
        $legacySecondClock = new GameClock(
            $base,
            1,
            0,
            GameClock::MODE_REALTIME,
            $base,
            static fn (): \DateTimeImmutable => $wallTimes[1],
        );
        $legacySecondNowTick = $legacySecondClock->nowTick();
        self::assertSame(562_884, $legacySecondNowTick);
        $legacySecondExpiry = $legacySecondNowTick + $clock->ticksFromSeconds(
            $message->validUntil->getTimestamp() - $message->date->getTimestamp(),
        );
        self::assertSame(9_007_199_254_762_884, $legacySecondExpiry);
        $senderTicks = $message->resolveTicksForTest($clock);

        self::assertSame([0, GameClock::MAX_SAFE_TICK], $receiverTicks);
        self::assertSame($receiverTicks, $senderTicks);
        self::assertSame(1, $wallReads);
    }

    public function testFiniteMessageCachesOneValidatedExpiryForBothCopies(): void
    {
        $base = new \DateTimeImmutable('2026-08-04 00:00:00.000000');
        $clock = new GameClock($base, 1, 120_000, GameClock::MODE_MANUAL, $base);
        $message = new TestableClockMessage(
            MessageType::national,
            $this->createMock(MessageTarget::class),
            $this->createMock(MessageTarget::class),
            '유한 메시지',
            new \DateTime('2026-08-04 00:00:02.000000'),
            new \DateTime('2026-08-04 00:01:02.000000'),
            [],
        );

        $receiverTicks = $message->resolveTicksForTest($clock);
        $senderTicks = $message->resolveTicksForTest($clock);

        self::assertSame([120_000, 36_120_000], $receiverTicks);
        self::assertSame($receiverTicks, $senderTicks);
    }
}

final class TestableClockMessage extends Message
{
    /** @return array{0:int, 1:int} */
    public function resolveTicksForTest(GameClock $clock): array
    {
        return $this->resolveSendTicks($clock);
    }
}
