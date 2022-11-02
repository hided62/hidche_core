<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\KVStorage;
use sammo\Util;
use sammo\General;
use sammo\JosaUtil;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;

class LostUniqueItem extends \sammo\Event\Action
{
  public function __construct(
    private float $lostProb = 0.1,
  ) {
  }

  public function run(array $env)
  {
    $db = DB::db();

    $generalIDList = $db->queryFirstColumn("SELECT `no` FROM general WHERE npc <= 1");
    if (!$generalIDList) {
      return;
    }
    $generals = General::createGeneralObjListFromDB($generalIDList);

    $lostItems = [];
    $totalLostCnt = 0;

    $year = $env['year'];
    $month = $env['month'];
    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
      UniqueConst::$hiddenSeed,
      'LostUniqueItem',
      $year,
      $month,
    )));

    $maxLostByGenCnt = 0;
    $maxLostGenList = [];

    foreach ($generals as $general) {
      $itemList = $general->getItems();
      $didLoseItem = false;
      $lostByGenCnt = 0;

      foreach ($itemList as $itemType => $item) {
        if ($item->isBuyable()) {
          continue;
        }


        $logger = $general->getLogger();

        if ($rng->nextBool($this->lostProb)) {
          $itemName = $item->getName();
          $itemRawName = $item->getRawName();
          $josaUl = JosaUtil::pick($itemRawName, '을');
          $lostItems[$itemName] = 1;
          $totalLostCnt++;
          $lostByGenCnt++;
          $general->setItem($itemType, 'None');
          $didLoseItem = true;
          $logger->pushGeneralActionLog("<C>{$itemName}</>{$josaUl} 잃었습니다.");
        }
      }

      if (!$didLoseItem) {
        continue;
      }

      $general->applyDB($db);

      if ($maxLostByGenCnt < $lostByGenCnt) {
        $maxLostByGenCnt = $lostByGenCnt;
        $maxLostGenList = [$general->getName()];
      } else if ($maxLostByGenCnt === $lostByGenCnt) {
        $maxLostGenList[] = $general->getName();
      }
    }

    $logger = new ActionLogger(0, 0, $year, $month);
    if ($totalLostCnt == 0) {
      $logger->pushGlobalHistoryLog("<R><b>【망실】</b></>어떤 아이템도 잃지 않았습니다!");
    } else {
      $genCnt = count($maxLostGenList);
      if($genCnt > 4){
        $maxLostGenList = array_slice($maxLostGenList, 0, 4);

      }
      $maxLostGenListStr = implode(', ', $maxLostGenList);

      if($genCnt > 4){
        $maxLostGenListStr .= ' 외 ' . ($genCnt - 4) . '명';
      }
      $josaYi = JosaUtil::pick($maxLostGenListStr, '이');

      $logger->pushGlobalHistoryLog("<R><b>【망실】</b></>불운하게도 <Y>{$maxLostGenListStr}</>{$josaYi} 한 번에 유니크 <C>{$maxLostByGenCnt}</>종을 잃었습니다! (총 <C>{$totalLostCnt}</>개)");
    }
    $logger->flush();
  }
}
