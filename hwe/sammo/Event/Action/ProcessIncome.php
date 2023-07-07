<?php

namespace sammo\Event\Action;

use RuntimeException;
use sammo\ActionLogger;
use sammo\DB;
use sammo\Enums\ResourceType;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\StringUtil;
use sammo\Util;

use function sammo\getBill;
use function sammo\getGoldIncome;
use function sammo\getOutcome;
use function sammo\getRiceIncome;
use function sammo\getWallIncome;
use function sammo\pushAdminLog;
use function sammo\tab2;

class ProcessIncome extends \sammo\Event\Action
{
  public function __construct(public string $resource)
  {
    if (ResourceType::tryFrom($resource) === null) {
      throw new RuntimeException('잘못된 자원 타입');
    }
  }

  private function processGoldIncome(array $env)
  {
    $db = DB::db();

    [$year, $month] = [$env['year'], $env['month']];
    $adminLog = [];


    $nationList = $db->query('SELECT name,nation,capital,gold,level,rate_tmp,bill,type from nation');
    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');
    $generalRawListByNation = Util::arrayGroupBy($db->query('SELECT no,name,nation,gold,officer_level,dedication,city FROM general WHERE npc != 5'), 'nation');

    //국가별 처리
    foreach ($nationList as $nation) {
      $nationID = $nation['nation'];

      $generalRawList = $generalRawListByNation[$nationID];
      $income = getGoldIncome($nationID, $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID] ?? []);
      $originoutcome = getOutcome(100, $generalRawList);
      $outcome = Util::round($nation['bill'] / 100 * $originoutcome);

      // 실제 지급량 계산
      $nation['gold'] += $income;
      // 기본량도 안될경우
      if ($nation['gold'] < GameConst::$basegold) {
        $realoutcome = 0;
        // 실지급률
        $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
      } elseif ($nation['gold'] - GameConst::$basegold < $outcome) {
        $realoutcome = $nation['gold'] - GameConst::$basegold;
        $nation['gold'] = GameConst::$basegold;
        // 실지급률
        $ratio = $realoutcome / $originoutcome;
      } else {
        $realoutcome = $outcome;
        $nation['gold'] -= $realoutcome;
        // 실지급률
        $ratio = $realoutcome / $originoutcome;
      }
      $nation['gold'] = Util::valueFit($nation['gold'], GameConst::$basegold);
      $adminLog[] = StringUtil::padStringAlignRight((string)$nation['name'], 12, " ")
        . " // 세금 : " . StringUtil::padStringAlignRight((string)$income, 6, " ")
        . " // 세출 : " . StringUtil::padStringAlignRight((string)$originoutcome, 6, " ")
        . " // 실제 : " . tab2((string)$realoutcome, 6, " ")
        . " // 지급률 : " . tab2((string)round($ratio * 100, 2), 5, " ")
        . " % // 결과금 : " . tab2((string)$nation['gold'], 6, " ");

      $incomeText = number_format($income);
      $incomeLog = "이번 수입은 금 <C>$incomeText</>입니다.";
      $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
      $nationStor->prev_income_gold = $income;

      $db->update('nation', [
        'gold' => $nation['gold']
      ], 'nation=%i', $nationID);

      // 각 장수들에게 지급
      foreach ($generalRawList as $rawGeneral) {
        $generalObj = new General($rawGeneral, null, null, null, null, $year, $month, false);
        $gold = Util::round(getBill($generalObj->getVar('dedication')) * $ratio);
        $generalObj->increaseVar('gold', $gold);

        $logger = $generalObj->getLogger();
        if ($generalObj->getVar('officer_level') > 4) {
          $logger->pushGeneralActionLog($incomeLog, $logger::PLAIN);
        }

        $goldText = number_format($gold);
        $logger->pushGeneralActionLog("봉급으로 금 <C>$goldText</>을 받았습니다.", $logger::PLAIN);
        $generalObj->applyDB($db);
      }
    }

    $logger = new ActionLogger(0, 0, $year, $month);
    $logger->pushGlobalHistoryLog('<W><b>【지급】</b></>봄이 되어 봉록에 따라 자금이 지급됩니다.');
    $logger->flush();

    pushAdminLog($adminLog);
  }

  private function processRiceIncome(array $env)
  {
    $db = DB::db();

    [$year, $month] = [$env['year'], $env['month']];
    $adminLog = [];

    $nationList = $db->query('SELECT name,level,nation,capital,rice,rate_tmp,bill,type from nation');
    $cityListByNation = Util::arrayGroupBy($db->query('SELECT * FROM city'), 'nation');
    $generalRawListByNation = Util::arrayGroupBy($db->query('SELECT no,name,nation,rice,officer_level,dedication,city FROM general WHERE npc != 5'), 'nation');

    //국가별 처리
    foreach ($nationList as $nation) {
      $nationID = $nation['nation'];

      $generalRawList = $generalRawListByNation[$nationID];
      $income = getRiceIncome($nation['nation'], $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID] ?? []);
      $income += getWallIncome($nation['nation'], $nation['level'], $nation['rate_tmp'], $nation['capital'], $nation['type'], $cityListByNation[$nationID] ?? []);
      $originoutcome = getOutcome(100, $generalRawList);
      $outcome = Util::round($nation['bill'] / 100 * $originoutcome);

      // 실제 지급량 계산
      $nation['rice'] += $income;
      // 기본량도 안될경우
      if ($nation['rice'] < GameConst::$baserice) {
        $realoutcome = 0;
        // 실지급률
        $ratio = 0;
        //기본량은 넘지만 요구량이 안될경우
      } elseif ($nation['rice'] - GameConst::$baserice < $outcome) {
        $realoutcome = $nation['rice'] - GameConst::$baserice;
        $nation['rice'] = GameConst::$baserice;
        // 실지급률
        $ratio = $realoutcome / $originoutcome;
      } else {
        $realoutcome = $outcome;
        $nation['rice'] -= $realoutcome;
        // 실지급률
        $ratio = $realoutcome / $originoutcome;
      }
      $nation['rice'] = Util::valueFit($nation['rice'], GameConst::$baserice);
      $adminLog[] = StringUtil::padStringAlignRight($nation['name'], 12, " ")
        . " // 세곡 : " . StringUtil::padStringAlignRight((string)$income, 6, " ")
        . " // 세출 : " . StringUtil::padStringAlignRight((string)$originoutcome, 6, " ")
        . " // 실제 : " . tab2((string)$realoutcome, 6, " ")
        . " // 지급률 : " . tab2((string)round($ratio * 100, 2), 5, " ")
        . " % // 결과곡 : " . tab2((string)$nation['rice'], 6, " ");

      $incomeText = number_format($income);
      $incomeLog = "이번 수입은 쌀 <C>$incomeText</>입니다.";
      $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
      $nationStor->prev_income_rice = $income;

      $db->update('nation', [
        'rice' => $nation['rice']
      ], 'nation=%i', $nationID);

      // 각 장수들에게 지급
      foreach ($generalRawList as $rawGeneral) {
        $generalObj = new General($rawGeneral, null, null, null, null, $year, $month, false);
        $rice = Util::round(getBill($generalObj->getVar('dedication')) * $ratio);
        $generalObj->increaseVar('rice', $rice);

        $logger = $generalObj->getLogger();
        if ($generalObj->getVar('officer_level') > 4) {
          $logger->pushGeneralActionLog($incomeLog, $logger::PLAIN);
        }
        $riceText = number_format($rice);
        $logger->pushGeneralActionLog("봉급으로 쌀 <C>$riceText</>을 받았습니다.", $logger::PLAIN);
        $generalObj->applyDB($db);
      }
    }

    $logger = new ActionLogger(0, 0, $year, $month);
    $logger->pushGlobalHistoryLog('<W><b>【지급】</b></>가을이 되어 봉록에 따라 군량이 지급됩니다.');
    $logger->flush();

    pushAdminLog($adminLog);
  }

  public function run(array $env)
  {
    if ($this->resource === ResourceType::gold->value) {
      $this->processGoldIncome($env);
    } else {
      $this->processRiceIncome($env);
    }
  }
}
