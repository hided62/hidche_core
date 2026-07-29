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

    public static function initialAux(array $targetInfo, ?array $userInitialStats = null): array
    {
        $granted = array_fill_keys(array_merge(self::STAT_KEYS, self::DEX_KEYS), 0);
        if ($userInitialStats !== null) {
            foreach (self::STAT_KEYS as $key) {
                $initial = (int) ($userInitialStats[$key] ?? GameConst::$defaultStatMin);
                $granted[$key] = max(0, $initial - min($initial, GameConst::$defaultStatMin));
            }
        }

        return [
            'targetId' => (string) ($targetInfo['uniqueName'] ?? ''),
            'granted' => $granted,
            'dexConsumed' => array_fill_keys(self::DEX_KEYS, 0),
            'dexFloor' => array_fill_keys(self::DEX_KEYS, 0),
            'progressMonth' => -1,
            'milestone' => 0,
            'naturalSpecialDomestic' => null,
            'eventSpecialDomestic' => null,
            'userInitialStats' => $userInitialStats,
            'dexTargetRatio' => 1.0,
        ];
    }

    public static function attachInitialTarget(GeneralBuilder $builder, array $targetInfo): void
    {
        $builder->setAuxVar(self::AUX_KEY, self::initialAux($targetInfo));
    }

    /**
     * Builds an ordinary-user stat total while preserving the selected
     * candidate's relative strengths as closely as integer stats allow.
     *
     * @return array{leadership:int,strength:int,intel:int}
     */
    public static function calculateUserInitialStats(array $targetInfo): array
    {
        $targets = [];
        $bases = [];
        foreach (self::STAT_KEYS as $key) {
            $target = min(
                GameConst::$defaultStatMax,
                max(0, (int) ($targetInfo[$key] ?? 0))
            );
            $targets[$key] = $target;
            $bases[$key] = min($target, GameConst::$defaultStatMin);
        }

        $targetTotal = array_sum($targets);
        $desiredTotal = min(GameConst::$defaultStatTotal, $targetTotal);
        $baseTotal = array_sum($bases);
        $capacityTotal = $targetTotal - $baseTotal;
        if ($capacityTotal <= 0 || $desiredTotal <= $baseTotal) {
            return $bases;
        }

        $ratio = ($desiredTotal - $baseTotal) / $capacityTotal;
        $result = [];
        $fractions = [];
        foreach (self::STAT_KEYS as $idx => $key) {
            $raw = $bases[$key] + ($targets[$key] - $bases[$key]) * $ratio;
            $result[$key] = (int) floor($raw);
            $fractions[] = [
                'key' => $key,
                'fraction' => $raw - $result[$key],
                'order' => $idx,
            ];
        }

        usort($fractions, static function (array $lhs, array $rhs): int {
            $fractionOrder = $rhs['fraction'] <=> $lhs['fraction'];
            return $fractionOrder !== 0 ? $fractionOrder : $lhs['order'] <=> $rhs['order'];
        });
        $remainder = $desiredTotal - array_sum($result);
        foreach ($fractions as $fraction) {
            if ($remainder <= 0) {
                break;
            }
            $key = $fraction['key'];
            if ($result[$key] >= $targets[$key]) {
                continue;
            }
            $result[$key]++;
            $remainder--;
        }

        return $result;
    }

    /**
     * Returns the event stat baseline that a newly selected target receives at
     * the supplied game date. Organic growth can still leave the actual stat
     * above this baseline.
     *
     * @return array{leadership:int,strength:int,intel:int}
     */
    public static function calculateUserCurrentTargetStats(
        array $targetInfo,
        array $env
    ): array {
        $initialStats = self::calculateUserInitialStats($targetInfo);
        $progress = self::calculateProgress(
            (int) $env['startyear'],
            (int) $env['year'],
            (int) $env['month']
        );
        $result = [];
        foreach (self::STAT_KEYS as $key) {
            $target = min(
                GameConst::$maxLevel,
                max(0, (int) ($targetInfo[$key] ?? 0))
            );
            $result[$key] = max(
                $initialStats[$key],
                CentennialAllStarGrowth::statFloor(
                    $target,
                    GameConst::$defaultStatMin,
                    $progress
                )
            );
        }
        return $result;
    }

    public static function prepareInitialUser(
        GeneralBuilder $builder,
        array $targetInfo
    ): void {
        $initialStats = self::calculateUserInitialStats($targetInfo);
        $builder->setStat(
            $initialStats['leadership'],
            $initialStats['strength'],
            $initialStats['intel']
        );
        $builder->setAuxVar(
            self::AUX_KEY,
            self::initialAux($targetInfo, $initialStats)
        );
    }

    /**
     * Old 100th-season characters did not distinguish their form-entered
     * initial stats from organic growth. Before their first reselection, treat
     * the ordinary creation range as the replaceable initial allocation.
     */
    public static function prepareLegacyUserReselection(General $general): void
    {
        $aux = $general->getAuxVar(self::AUX_KEY);
        if (!is_array($aux) || is_array($aux['userInitialStats'] ?? null)) {
            return;
        }

        $granted = is_array($aux['granted'] ?? null)
            ? $aux['granted']
            : array_fill_keys(array_merge(self::STAT_KEYS, self::DEX_KEYS), 0);
        $legacyInitialStats = [];
        foreach (self::STAT_KEYS as $key) {
            $current = (int) $general->getVar($key);
            $granted[$key] = self::calculateLegacyUserGrant(
                $current,
                (int) ($granted[$key] ?? 0)
            );
            $beforeEventGrant = max(
                0,
                $current - max(0, (int) ($aux['granted'][$key] ?? 0))
            );
            $legacyInitialStats[$key] = min(
                $beforeEventGrant,
                GameConst::$defaultStatMax
            );
        }
        $aux['granted'] = $granted;
        $aux['userInitialStats'] = $legacyInitialStats;
        $general->setAuxVar(self::AUX_KEY, $aux);
    }

    public static function calculateLegacyUserGrant(int $current, int $eventGrant): int
    {
        $eventGrant = max(0, $eventGrant);
        $beforeEventGrant = max(0, $current - $eventGrant);
        $replaceableInitialGrant = max(
            0,
            min($beforeEventGrant, GameConst::$defaultStatMax)
                - min($beforeEventGrant, GameConst::$defaultStatMin)
        );
        return $eventGrant + $replaceableInitialGrant;
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

    public static function calculateDexTargetFloor(
        int $target,
        array $env,
        float $targetRatio = 1.0
    ): int {
        if ($targetRatio < 0 || $targetRatio > 1) {
            throw new \InvalidArgumentException('dex target ratio must be between 0 and 1');
        }
        $target = min(GameConst::$dexLimit, max(0, $target));
        $scaledTarget = (int) floor($target * $targetRatio);
        $progress = self::calculateProgress(
            (int) $env['startyear'],
            (int) $env['year'],
            (int) $env['month']
        );
        return CentennialAllStarGrowth::dexFloor($scaledTarget, $progress);
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
        float $progressMultiplier = 1.0,
        float $dexTargetRatio = 1.0
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
        $dexConsumed = is_array($aux['dexConsumed'] ?? null)
            ? $aux['dexConsumed']
            : array_fill_keys(self::DEX_KEYS, 0);
        if ($targetChanged) {
            $dexConsumed = array_fill_keys(self::DEX_KEYS, 0);
        }
        $dexFloor = is_array($aux['dexFloor'] ?? null)
            ? $aux['dexFloor']
            : array_fill_keys(self::DEX_KEYS, 0);
        $isUserTarget = is_array($aux['userInitialStats'] ?? null);
        $nextUserInitialStats = $targetChanged && $isUserTarget
            ? self::calculateUserInitialStats($targetInfo)
            : ($aux['userInitialStats'] ?? null);
        $previousDexTargetRatio = is_numeric($aux['dexTargetRatio'] ?? null)
            ? (float) $aux['dexTargetRatio']
            : 1.0;
        $dexTargetRatioChanged = $previousDexTargetRatio !== $dexTargetRatio;
        $userCurrentTargetStats = $isUserTarget
            ? self::calculateUserCurrentTargetStats($targetInfo, $env)
            : null;
        $changed = $dexTargetRatioChanged;

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
            if ($userCurrentTargetStats !== null) {
                $floor = $userCurrentTargetStats[$key];
            }
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
            $floor = max(
                0,
                self::calculateDexTargetFloor(
                    (int) $targetDex[$idx],
                    $env,
                    $dexTargetRatio
                ) - max(0, (int) ($dexConsumed[$key] ?? 0))
            );
            $dexFloor[$key] = $floor;
            $current = (int) $general->getVar($key);
            if ($targetChanged || $dexTargetRatioChanged) {
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
        $aux['dexConsumed'] = $dexConsumed;
        $aux['dexFloor'] = $dexFloor;
        $aux['progressMonth'] = max((int) ($aux['progressMonth'] ?? -1), $progressMonth);
        $aux['milestone'] = max($previousMilestone, $milestone);
        $aux['userInitialStats'] = $nextUserInitialStats;
        $aux['dexTargetRatio'] = $dexTargetRatio;
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

    public static function dexTargetRatioForNPCType(int $npcType): float
    {
        return in_array($npcType, [3, 4], true)
            ? GameConst::$centennialNpcDexTargetRatio
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
            self::NPC_PROGRESS_MULTIPLIER,
            GameConst::$centennialNpcDexTargetRatio
        );
        $general->applyDB($db);
        return $result;
    }

    /**
     * Moves the event-backed part of a dex conversion with the converted
     * value and consumes any guaranteed floor crossed by the source value.
     * This keeps the monthly floor from refilling points already converted.
     */
    public static function reconcileDexConversion(
        General $general,
        string $sourceKey,
        string $destinationKey,
        int $sourceBefore,
        int $sourceAfter,
        int $destinationBefore,
        int $destinationAfter,
        float $convertCoeff
    ): void {
        if (!in_array($sourceKey, self::DEX_KEYS, true)
            || !in_array($destinationKey, self::DEX_KEYS, true)
            || $sourceKey === $destinationKey
        ) {
            throw new \InvalidArgumentException('invalid dex conversion keys');
        }
        if ($convertCoeff < 0 || $convertCoeff > 1) {
            throw new \InvalidArgumentException('dex conversion coefficient must be between 0 and 1');
        }

        $sourceDecrease = max(0, $sourceBefore - $sourceAfter);
        $destinationIncrease = max(0, $destinationAfter - $destinationBefore);
        if ($sourceDecrease === 0 && $destinationIncrease === 0) {
            return;
        }

        $aux = $general->getAuxVar(self::AUX_KEY);
        if (!is_array($aux)) {
            return;
        }
        $granted = is_array($aux['granted'] ?? null)
            ? $aux['granted']
            : array_fill_keys(array_merge(self::STAT_KEYS, self::DEX_KEYS), 0);
        $dexConsumed = is_array($aux['dexConsumed'] ?? null)
            ? $aux['dexConsumed']
            : array_fill_keys(self::DEX_KEYS, 0);
        $dexFloor = is_array($aux['dexFloor'] ?? null)
            ? $aux['dexFloor']
            : [];

        $sourceGrantedBefore = min(
            max(0, $sourceBefore),
            max(0, (int) ($granted[$sourceKey] ?? 0))
        );
        $sourceOrganicBefore = max(0, $sourceBefore - $sourceGrantedBefore);
        $sourceGrantedAfter = max(
            0,
            $sourceAfter - min($sourceAfter, $sourceOrganicBefore)
        );
        $eventGrantRemoved = max(0, $sourceGrantedBefore - $sourceGrantedAfter);

        $destinationGrantedBefore = min(
            max(0, $destinationBefore),
            max(0, (int) ($granted[$destinationKey] ?? 0))
        );
        $eventGrantTransferred = min(
            $destinationIncrease,
            (int) floor($eventGrantRemoved * $convertCoeff)
        );
        $granted[$sourceKey] = $sourceGrantedAfter;
        $granted[$destinationKey] = min(
            max(0, $destinationAfter),
            $destinationGrantedBefore + $eventGrantTransferred
        );

        $sourceFloor = max(
            0,
            (int) ($dexFloor[$sourceKey] ?? $sourceBefore)
        );
        $gapBefore = max(0, $sourceFloor - $sourceBefore);
        $gapAfter = max(0, $sourceFloor - $sourceAfter);
        $dexConsumed[$sourceKey] = max(
            0,
            (int) ($dexConsumed[$sourceKey] ?? 0)
                + max(0, $gapAfter - $gapBefore)
        );

        $aux['granted'] = $granted;
        $aux['dexConsumed'] = $dexConsumed;
        $general->setAuxVar(self::AUX_KEY, $aux);
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
