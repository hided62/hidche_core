<?php

namespace sammo;

use sammo\VO\InheritancePointType;
use Ds\Map;
use sammo\Enums\InheritanceKey;
use sammo\Enums\RankColumn;

class InheritancePointManager
{
  /** @var Map<InheritanceKey,InheritancePointType> */
  public readonly Map $inheritanceKey;

  private static self|null $instance = null;

  private function __construct()
  {
    $inheritanceKey = new Map();
    $inheritanceKey->put(InheritanceKey::previous, new InheritancePointType(true, 1, '기존 포인트', 1));
    $inheritanceKey->put(InheritanceKey::lived_month, new InheritancePointType(true, 1, '생존', 1));
    $inheritanceKey->put(InheritanceKey::max_belong, new InheritancePointType(false, 10, '최대 임관년 수', null));
    $inheritanceKey->put(InheritanceKey::max_domestic_critical, new InheritancePointType(true, 1, '최대 연속 내정 성공', null));
    $inheritanceKey->put(InheritanceKey::active_action, new InheritancePointType(true, 3, '능동 행동 수', 1));
    //$inheritanceKey->put(InheritanceKey::snipe_combat, new InheritancePointType(true, 10, '병종 상성 우위 횟수'));
    $inheritanceKey->put(InheritanceKey::combat, new InheritancePointType(['rank', RankColumn::warnum], 5, '전투 횟수', 1));
    $inheritanceKey->put(InheritanceKey::sabotage, new InheritancePointType(['rank', RankColumn::firenum], 20, '계략 성공 횟수', 1));
    $inheritanceKey->put(InheritanceKey::unifier, new InheritancePointType(true, 1, '천통 기여', null));
    $inheritanceKey->put(InheritanceKey::dex, new InheritancePointType(false, 0.001, '숙련도', 0.5));
    $inheritanceKey->put(InheritanceKey::tournament, new InheritancePointType(true, 1, '토너먼트', 1));
    $inheritanceKey->put(InheritanceKey::betting, new InheritancePointType(false, 10, '베팅 당첨', 1));
    $this->inheritanceKey = $inheritanceKey;
  }

  public static function getInstance(): self
  {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getInheritancePointType(InheritanceKey $key): InheritancePointType
  {
    $value = $this->inheritanceKey[$key];
    if ($value === null) {
      throw new \OutOfRangeException("{$key->value}는 유산 타입이 아님");
    }
    return $value;
  }

  /**
   * @param array<General> $generals
   * @return Map<int, int|float>
   */
  public function getInheritancePointFromAll(array $generals, InheritanceKey $key, ?array &$aux = null, bool $forceCalc = false): Map
  {
    $inheritType = $this->getInheritancePointType($key);

    $storeType = $inheritType->storeType;
    $multiplier = $inheritType->pointCoeff;

    $result = new Map();

    $db = DB::db();

    $gameStor = KVStorage::getStorage($db, 'game_env');
    if ($storeType === true || ($gameStor->isunited != 0 && !$forceCalc)) {
      $ownerMap = [];
      foreach ($generals as $general) {
        $ownerMap[$general->getVar('owner')] = $general->getID();
      }

      (array_map(fn (General $gen) => $gen->getID(), $generals));
      $ownerMap = Util::convertPairArrayToDict($db->queryAllLists('SELECT `owner`, `no` FROM `general`'));
      foreach (KVStorage::getValuesFromInterNamespace($db, "storage", $key) as $namespace => $value) {
        if (!str_starts_with($namespace, 'inheritance_')) {
          continue;
        }
        $userID = Util::toInt(substr($namespace, strlen('inheritance_')));
        if (!key_exists($userID, $ownerMap)) {
          continue;
        }
        [$value,] = $value;

        $result[$ownerMap[$userID]] = $value;
      }

      return $result;
    }

    $auxTmp = [];
    foreach ($generals as $general) {
      $generalID = $general->getID();
      $auxSub = null;
      $value = $this->getInheritancePoint($general, $key, $auxSub, $forceCalc);
      $auxTmp[$generalID] = $auxSub;
      $result[$generalID] = $value;
    }
    $aux = $auxTmp;

    return $result;
  }

  public function getInheritancePoint(General $general, InheritanceKey $key, &$aux = null, bool $forceCalc = false): int|float|null
  {
    $inheritType = $this->getInheritancePointType($key);

    $ownerID = $general->getVar('owner');
    if (!$ownerID) {
      return 0;
    }

    if ($general->getVar('npc') >= 2) {
      return 0;
    }

    $storeType = $inheritType->storeType;
    $multiplier = $inheritType->pointCoeff;

    $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
    if ($storeType === true || ($gameStor->isunited != 0 && !$forceCalc)) {
      $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
      [$value, $aux] = $inheritStor->getValue($key) ?? [0, null];
      return $value;
    }

    if (is_array($storeType)) {
      [$storSubType, $storSubKey] = $storeType;
      if ($storSubType === 'rank') {
        return $general->getRankVar($storSubKey) * $multiplier;
      }
      if ($storSubType === 'raw') {
        return $general->getVar($storSubKey) * $multiplier;
      }
      if ($storSubType === 'aux') {
        return ($general->getAuxVar($storSubKey) ?? 0) * $multiplier;
      }
      throw new \InvalidArgumentException("{$storSubType}은 참조 할 수 없는 유산 세부키임");
    }

    if ($storeType !== false) {
      throw new \InvalidArgumentException("{$storeType}은 올바르지 않은 유산 키임");
    }

    $extractFn = function () {
      return [0, null];
    };
    switch ($key) {
      case InheritanceKey::dex:
        $extractFn = function () use ($general, $multiplier) {
          $dexLimit = Util::array_last(getDexLevelList())[0];
          $totalDex = 0;
          foreach (array_keys(GameUnitConst::allType()) as $armType) {
            $subDex = $general->getVar("dex{$armType}");
            if ($subDex > $dexLimit) {
              $totalDex += ($subDex - $dexLimit) / 3;
              $subDex = $dexLimit;
            }
            $totalDex += $subDex;
          }
          return [$totalDex * $multiplier, null];
        };
        break;
      case InheritanceKey::betting:
        $extractFn = function () use ($general, $multiplier) {
          $betWin = $general->getRankVar(RankColumn::betwin);
          $betWinRate = $general->getRankVar(RankColumn::betwingold) / max(1, $general->getRankVar(RankColumn::betgold));

          return [$betWin * $multiplier * pow($betWinRate, 2), null];
        };
        break;
      case InheritanceKey::max_belong:
        $extractFn = function () use ($general, $multiplier) {
          $maxBelong = max($general->getVar('belong'), $general->getAuxVar(InheritanceKey::max_belong->value) ?? 0);
          return [$maxBelong * $multiplier, null];
        };
        break;
      default:
        throw new \InvalidArgumentException("{$key->value}는 유산 추출기를 보유하고 있지 않음");
    }

    [$value, $aux] = ($extractFn)();
    return $value;
  }


  public function setInheritancePoint(General $general, InheritanceKey $key, $value, $aux = null)
  {
    if (!is_int($value) && !is_float($value)) {
      throw new \InvalidArgumentException("{$value}는 숫자가 아님");
    }
    $inheritType = InheritancePointManager::getInstance()->getInheritancePointType($key);

    $storeType = $inheritType->storeType;
    $multiplier = $inheritType->pointCoeff;
    if ($storeType !== true) {
      throw new \InvalidArgumentException("{$key->value}는 직접 저장형 유산 포인트가 아님");
    }
    if ($multiplier != 1 && $value != 0) {
      throw new \InvalidArgumentException("{$key->value}는 1:1 유산 포인트가 아님");
    }

    $ownerID = $general->getVar('owner');
    if (!$ownerID) {
      return;
    }

    if ($general->getVar('npc') >= 2) {
      return;
    }

    $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
    if ($gameStor->isunited != 0) {
      return;
    }

    $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
    $inheritStor->setValue($key, [$value, $aux]);
  }

  public function increaseInheritancePoint(General $general, InheritanceKey $key, $value, $aux = null)
  {
    if (!is_int($value) && !is_float($value)) {
      throw new \InvalidArgumentException("{$value}는 숫자가 아님");
    }

    $inheritType = InheritancePointManager::getInstance()->getInheritancePointType($key);

    $storeType = $inheritType->storeType;
    $multiplier = $inheritType->pointCoeff;
    if ($storeType !== true) {
      throw new \InvalidArgumentException("{$key->value}는 직접 저장형 유산 포인트가 아님");
    }

    $ownerID = $general->getVar('owner');
    if (!$ownerID) {
      return;
    }

    if ($general->getVar('npc') >= 2) {
      return;
    }

    $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
    if ($gameStor->isunited != 0) {
      return;
    }

    $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
    [$oldValue, $oldAux] = $inheritStor->getValue($key->value) ?? [0, null];

    if ($oldAux !== $aux) {
      $oldValue = 0;
    }

    $newValue = $oldValue + $value * $multiplier;
    $inheritStor->setValue($key->value, [$newValue, $aux]);
  }

  public function clearInheritancePoint(?int $ownerID)
  {
    if (!$ownerID) {
      return;
    }

    $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
    $allPoints = $inheritStor->getAll();
    if (!$allPoints || count($allPoints) == 0) {
      //비었으므로 리셋 안함
      return;
    }
    if (count($allPoints) == 1 && key_exists(InheritanceKey::previous->value, $allPoints)) {
      //이미 리셋되었으므로 리셋 안함
      return;
    }

    $previousPointInfo = $allPoints[InheritanceKey::previous->value];
    $inheritStor->resetValues();
    $inheritStor->setValue(InheritanceKey::previous->value, $previousPointInfo);
  }

  public function mergeTotalInheritancePoint(General $general, bool $isRebirth = false)
  {
    $ownerID = $general->getVar('owner');
    if (!$ownerID) {
      return;
    }

    if ($general->getVar('npc') > 1) {
      return;
    }

    $gameStor = KVStorage::getStorage(DB::db(), 'game_env');
    $gameStor->cacheValues(['year', 'startyear', 'month']);

    if ($general->getVar('npc') == 1) {
      if ($isRebirth) {
        return;
      }

      $pickYearMonth = $general->getAuxVar('pickYearMonth');
      if ($pickYearMonth === null) {
        return;
      }
      [$startYear, $year] = $gameStor->getValuesAsArray(['startyear', 'year']);

      if (($year - Util::parseYearMonth($pickYearMonth)[0]) * 2 <= ($year - $startYear)) {
        return;
      }
    }

    $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
    $inheritStor->cacheAll();
    foreach ($this->inheritanceKey as $key => $keyObj) {
      $storeType = $keyObj->storeType;
      $aux = null;
      $point = $general->getInheritancePoint($key, $aux, true);
      if ($storeType === true) {
        continue;
      }
      $inheritStor->setValue($key, [$point, $aux]);
    }

    $oldInheritStor = KVStorage::getStorage(DB::db(), "inheritance_result");
    $serverID = UniqueConst::$serverID;
    $year = $gameStor->year;
    $month = $gameStor->month;
    $oldInheritStor->setValue("{$serverID}_{$ownerID}_{$general->getID()}_{$year}_{$month}", $inheritStor->getAll(true));
  }


  function applyInheritanceUser(int $ownerID, bool $isRebirth = false): float
  {
    if ($ownerID === 0) {
      return 0;
    }

    //FIXME: 굳이 merge, apply, clean 3단계를 거쳐야 할 이유가 없음
    $inheritStor = KVStorage::getStorage(DB::db(), "inheritance_{$ownerID}");
    $totalPoint = 0;
    /** @var array<string,array{0:float,1:string|float}> */
    $allPoints = $inheritStor->getAll();
    if (!$allPoints || count($allPoints) == 0) {
      //비었으므로 리셋 안함
      return 0;
    }
    if (count($allPoints) == 1 && key_exists(InheritanceKey::previous->value, $allPoints)) {
      //이미 리셋되었으므로 리셋 안함
      return $allPoints[InheritanceKey::previous->value][0];
    }

    $userLogger = new UserLogger($ownerID);

    $previousPoint = ($allPoints[InheritanceKey::previous->value] ?? [0, 0])[0];

    $keepValues = [];

    foreach ($allPoints as $rKey => [$value, $auxV]) {
      $key = InheritanceKey::from($rKey);

      /** @var InheritancePointType */
      $keyTypeObj = $this->inheritanceKey->get($key);

      if ($isRebirth) {
        if ($keyTypeObj->rebirthStoreCoeff === null) {
          $keepValues[$rKey] = [$value, $auxV];
          continue;
        }

        $value *= $keyTypeObj->rebirthStoreCoeff;
      }

      $keyText = $this->getInheritancePointType($key)->info;
      $userLogger->push("{$keyText} 포인트 {$value} 증가", "inheritPoint");
      $totalPoint += $value;
    }
    $totalPoint = Util::toInt($totalPoint);
    $userLogger->push("포인트 {$previousPoint} => {$totalPoint}", "inheritPoint");
    $userLogger->flush();

    $inheritStor->resetValues();

    foreach ($keepValues as $rKey => $pointPair) {
      $inheritStor->setValue($rKey, $pointPair);
    }
    $inheritStor->setValue(InheritanceKey::previous->value, [$totalPoint, null]);
    return $totalPoint;
  }
}
