<?php

namespace sammo;

use sammo\Scenario\GeneralBuilder;

final class CentennialAllStarGrowthService
{
    public const POOL_CLASS = 'SPoolUnderU100';
    public const AUX_KEY = 'event100_allstar';
    public const TRAIT_UNLOCK_PROGRESS = 0.4;
    public const NPC_PROGRESS_MULTIPLIER = 0.9;

    private const STAT_KEYS = ['leadership', 'strength', 'intel'];
    private const DEX_KEYS = ['dex1', 'dex2', 'dex3', 'dex4', 'dex5'];

    public static function isActive(): bool
    {
        return GameConst::$targetGeneralPool === self::POOL_CLASS;
    }

    public static function initialAux(array $targetInfo): array
    {
        return [
            'targetId' => (string) ($targetInfo['uniqueName'] ?? ''),
            'granted' => array_fill_keys(array_merge(self::STAT_KEYS, self::DEX_KEYS), 0),
            'progressMonth' => -1,
            'milestone' => 0,
            'naturalSpecialDomestic' => null,
            'eventSpecialDomestic' => null,
        ];
    }

    public static function attachInitialTarget(GeneralBuilder $builder, array $targetInfo): void
    {
        $builder->setAuxVar(self::AUX_KEY, self::initialAux($targetInfo));
    }

    public static function calculateProgress(
        int $startYear,
        int $year,
        int $month,
        float $progressMultiplier = 1.0
    ): float {
        if ($progressMultiplier < 0 || $progressMultiplier > 1) {
            throw new \InvalidArgumentException('progress multiplier must be between 0 and 1');
        }
        return min(
            1,
            CentennialAllStarGrowth::progress($startYear, $year, $month)
                * $progressMultiplier
        );
    }

    /**
     * Mutates the General object but leaves persistence to the caller.
     *
     * @return array{progress:float,milestone:int,previousMilestone:int,targetChanged:bool,changed:bool}
     */
    public static function applyTarget(
        General $general,
        array $targetInfo,
        array $env,
        float $progressMultiplier = 1.0
    ): array
    {
        $startYear = (int) $env['startyear'];
        $year = (int) $env['year'];
        $month = (int) $env['month'];
        $progress = self::calculateProgress(
            $startYear,
            $year,
            $month,
            $progressMultiplier
        );
        $progressMonth = (int) floor(
            max(0, ($year - $startYear) * 12 + $month - 1)
                * $progressMultiplier
        );
        $targetId = (string) ($targetInfo['uniqueName'] ?? '');

        $aux = $general->getAuxVar(self::AUX_KEY);
        if (!is_array($aux)) {
            $aux = self::initialAux($targetInfo);
        }
        $granted = is_array($aux['granted'] ?? null)
            ? $aux['granted']
            : array_fill_keys(array_merge(self::STAT_KEYS, self::DEX_KEYS), 0);
        $targetChanged = ($aux['targetId'] ?? '') !== $targetId;
        $changed = false;

        foreach (self::STAT_KEYS as $key) {
            if (!array_key_exists($key, $targetInfo)) {
                continue;
            }
            $target = min(GameConst::$maxLevel, max(0, (int) $targetInfo[$key]));
            $floor = CentennialAllStarGrowth::statFloor(
                $target,
                GameConst::$defaultStatMin,
                $progress
            );
            $current = (int) $general->getVar($key);
            if ($targetChanged) {
                $result = CentennialAllStarGrowth::replaceTarget(
                    $current,
                    (int) ($granted[$key] ?? 0),
                    $floor
                );
            } else {
                $result = CentennialAllStarGrowth::advance(
                    $current,
                    (int) ($granted[$key] ?? 0),
                    $floor
                );
            }
            if ($result['value'] !== $current) {
                $general->updateVar($key, $result['value']);
                $changed = true;
            }
            $granted[$key] = $result['granted'];
        }

        $targetDex = $targetInfo['dex'] ?? [];
        foreach (self::DEX_KEYS as $idx => $key) {
            if (!array_key_exists($idx, $targetDex)) {
                continue;
            }
            $target = min(GameConst::$dexLimit, max(0, (int) $targetDex[$idx]));
            $floor = CentennialAllStarGrowth::dexFloor($target, $progress);
            $current = (int) $general->getVar($key);
            if ($targetChanged) {
                $result = CentennialAllStarGrowth::replaceTarget(
                    $current,
                    (int) ($granted[$key] ?? 0),
                    $floor
                );
            } else {
                $result = CentennialAllStarGrowth::advance(
                    $current,
                    (int) ($granted[$key] ?? 0),
                    $floor
                );
            }
            if ($result['value'] !== $current) {
                $general->updateVar($key, $result['value']);
                $changed = true;
            }
            $granted[$key] = $result['granted'];
        }

        $oldEventSpecial = $aux['eventSpecialDomestic'] ?? null;
        if ($targetChanged && $oldEventSpecial !== null
            && $general->getVar('special') === $oldEventSpecial
        ) {
            $general->updateVar(
                'special',
                $aux['naturalSpecialDomestic'] ?? GameConst::$defaultSpecialDomestic
            );
            $changed = true;
            $aux['eventSpecialDomestic'] = null;
        }

        $targetSpecial = $targetInfo['specialDomestic'] ?? null;
        if ($progress >= self::TRAIT_UNLOCK_PROGRESS && is_string($targetSpecial) && $targetSpecial !== '') {
            if (($aux['naturalSpecialDomestic'] ?? null) === null) {
                $aux['naturalSpecialDomestic'] = $general->getVar('special');
            }
            if ($general->getVar('special') !== $targetSpecial) {
                $general->updateVar('special', $targetSpecial);
                $changed = true;
            }
            $aux['eventSpecialDomestic'] = $targetSpecial;
        }

        $previousMilestone = (int) ($aux['milestone'] ?? 0);
        $milestone = min(5, (int) floor($progress * 5 + 0.0000001));
        $aux['targetId'] = $targetId;
        $aux['granted'] = $granted;
        $aux['progressMonth'] = max((int) ($aux['progressMonth'] ?? -1), $progressMonth);
        $aux['milestone'] = max($previousMilestone, $milestone);
        $general->setAuxVar(self::AUX_KEY, $aux);

        return [
            'progress' => $progress,
            'milestone' => $milestone,
            'previousMilestone' => $previousMilestone,
            'targetChanged' => $targetChanged,
            'changed' => $changed || $targetChanged || $milestone > $previousMilestone,
        ];
    }

    public static function progressMultiplierFor(General $general): float
    {
        return self::progressMultiplierForNPCType($general->getNPCType());
    }

    public static function progressMultiplierForNPCType(int $npcType): float
    {
        return in_array($npcType, [3, 4], true)
            ? self::NPC_PROGRESS_MULTIPLIER
            : 1.0;
    }

    public static function applyCurrentTargetToBuiltNPC(
        \MeekroDB $db,
        GeneralBuilder $builder,
        array $targetInfo,
        array $env
    ): ?array {
        if (!self::isActive()) {
            return null;
        }

        $general = General::createObjFromDB($builder->getGeneralID());
        if (!in_array($general->getNPCType(), [3, 4], true)) {
            return null;
        }
        $result = self::applyTarget(
            $general,
            $targetInfo,
            $env,
            self::NPC_PROGRESS_MULTIPLIER
        );
        $general->applyDB($db);
        return $result;
    }

    public static function recordableValue(General $general, string $key): int
    {
        $aux = $general->getAuxVar(self::AUX_KEY);
        $granted = is_array($aux) && is_array($aux['granted'] ?? null)
            ? (int) ($aux['granted'][$key] ?? 0)
            : 0;
        return CentennialAllStarGrowth::recordableValue((int) $general->getVar($key), $granted);
    }

    public static function recordableRawValue(array $general, string $key): int
    {
        $aux = Json::decode($general['aux'] ?? '{}');
        $eventAux = is_array($aux[self::AUX_KEY] ?? null) ? $aux[self::AUX_KEY] : [];
        $granted = is_array($eventAux['granted'] ?? null)
            ? (int) ($eventAux['granted'][$key] ?? 0)
            : 0;
        return CentennialAllStarGrowth::recordableValue((int) ($general[$key] ?? 0), $granted);
    }
}
