<?php

namespace sammo\Event\Action;

use sammo\ActionLogger;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\InheritanceKey;
use sammo\GameConst;
use sammo\General;
use sammo\JosaUtil;
use sammo\Json;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\getNationChiefLevel;
use function sammo\getNationLevel;
use function sammo\giveRandomUniqueItem;

class UpdateNationLevel extends \sammo\Event\Action
{
  public function run(array $env)
  {
    $db = DB::db();

    $year = $env['year'];
    $month = $env['month'];
    $startYear = $env['startyear'];


    $nationCityCounts = [];
    foreach ($db->queryAllLists('SELECT nation, count(*) FROM city WHERE LEVEL>=4 GROUP BY nation') as [$nationID, $cityCnt]) {
      $nationCityCounts[$nationID] = $cityCnt;
    }


    $nationLevelByCityCnt = [
      0, //방랑군
      1, //호족
      2, //군벌
      5, //주자사
      8, //주목
      11, //공
      16, //왕
      21, //황제
    ];

    foreach ($db->query('SELECT nation,name,level,tech,aux FROM nation') as $nation) {
      //TODO: level이 진관수이소중대특 체계를 벗어날 수 있음
      $nationID = $nation['nation'];
      $cityCnt = $nationCityCounts[$nationID] ?? 0;

      /** @var int */
      $nationlevel = 0;
      foreach ($nationLevelByCityCnt as $cmpNationLevel => $cmpCityCnt) {
        if ($cityCnt < $cmpCityCnt) {
          break;
        }
        $nationlevel = $cmpNationLevel;
      }

      if ($nationlevel > $nation['level']) {
        $levelDiff = $nationlevel - $nation['level'];
        $oldLevel = $nation['level'];
        $nation['level'] = $nationlevel;

        $updateVals = [
          'level' => $nationlevel,
          'gold' => $db->sqleval('gold + %i', $nationlevel * 1000),
          'rice' => $db->sqleval('rice + %i', $nationlevel * 1000),
        ];

        $nationName = $nation['name'];
        $lordName = $db->queryFirstField('SELECT name FROM general WHERE nation = %i AND officer_level = 12', $nationID);

        $oldNationLevelText = getNationLevel($oldLevel);
        $nationLevelText = getNationLevel($nationlevel);

        $logger = new ActionLogger(0, $nationID, $year, $month, false);
        $josaYi = JosaUtil::pick($lordName, '이');

        switch ($nationlevel) {
          case 7: //황제
            $josaRo = JosaUtil::pick($nationLevelText, '로');
            $logger->pushGlobalHistoryLog("<Y><b>【작위】</b></><D><b>{$nationName}</b></> {$oldNationLevelText} <Y>{$lordName}</>{$josaYi} <C>{$nationLevelText}</>{$josaRo} 옹립되었습니다.");
            $logger->pushNationalHistoryLog("<D><b>{$nationName}</b></> {$oldNationLevelText} <Y>{$lordName}</>{$josaYi} <C>{$nationLevelText}</>{$josaRo} 옹립");
            $auxVal = Json::decode($nation['aux']);
            $auxVal['can_국기변경'] = 1;
            $auxVal['can_국호변경'] = 1;
            $updateVals['aux'] = Json::encode($auxVal);
            break;
          case 6: //왕
            $josaRo = JosaUtil::pick($nationLevelText, '로');
            $logger->pushGlobalHistoryLog("<Y><b>【작위】</b></><D><b>{$nationName}</b></>의 <Y>{$lordName}</>{$josaYi} <C>$nationLevelText</>{$josaRo} 책봉되었습니다.");
            $logger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$lordName}</>{$josaYi} <C>$nationLevelText</>{$josaRo} 책봉");
            break;
          case 5: //공
          case 4: //주목
          case 3: //주자사
            $josaRo = JosaUtil::pick($nationLevelText, '로');
            $logger->pushGlobalHistoryLog("<Y><b>【작위】</b></><D><b>{$nationName}</b></>의 <Y>{$lordName}</>{$josaYi} <C>$nationLevelText</>{$josaRo} 임명되었습니다.");
            $logger->pushNationalHistoryLog("<D><b>{$nationName}</b></>의 <Y>{$lordName}</>{$josaYi} <C>$nationLevelText</>{$josaRo} 임명됨");
            break;
          case 2: //군벌
            $josaRa = JosaUtil::pick($nationName, '라');
            $josaRo = JosaUtil::pick($nationLevelText, '로');
            $logger->pushGlobalHistoryLog("<Y><b>【작위】</b></><Y>{$lordName}</>{$josaYi} 독립하여 <D><b>{$nationName}</b></>{$josaRa}는 <C>$nationLevelText</>{$josaRo} 나섰습니다.");
            $logger->pushNationalHistoryLog("<Y>{$lordName}</>{$josaYi} 독립하여 <D><b>{$nationName}</b></>{$josaRa}는 <C>$nationLevelText</>{$josaRo} 나서다");
            break;
        }

        $db->update('nation', $updateVals, 'nation=%i', $nation['nation']);
        $logger->flush();

        $turnRows = [];
        foreach (Util::range(getNationChiefLevel($nation['level']), 12) as $chiefLevel) {
          foreach (Util::range(GameConst::$maxChiefTurn) as $turnIdx) {
            $turnRows[] = [
              'nation_id' => $nation['nation'],
              'officer_level' => $chiefLevel,
              'turn_idx' => $turnIdx,
              'action' => '휴식',
              'arg' => null,
              'brief' => '휴식'
            ];
          }
        }
        $db->insertIgnore('nation_turn', $turnRows);

        if ($levelDiff) {
          //유니크 아이템 하나 돌리자
          $targetKillTurn = $env['killturn'];
          $targetKillTurn -= 24 * 60 / $env['turnterm'];
          $nationGenIDList = $db->queryFirstColumn(
            'SELECT no FROM general WHERE nation = %i AND killturn >= %i AND npc < 2',
            $nation['nation'],
            $targetKillTurn
          );
          $nationGenList = General::createGeneralObjListFromDB($nationGenIDList, ['belong', 'npc', 'aux'], 2);
          $chiefID = null;

          $uniqueLotteryWeightList = [];

          $relYear = $year - $startYear;
          $maxTrialCountByYear = 1;
          foreach (GameConst::$maxUniqueItemLimit as $tmpVals) {
            [$targetYear, $targetTrialCnt] = $tmpVals;
            if ($relYear < $targetYear) {
              break;
            }
            $maxTrialCountByYear = $targetTrialCnt;
          }
          foreach ($nationGenList as $nationGen) {
            if ($nationGen->getVar('officer_level') == 12) {
              $chiefID = $nationGen->getID();
            }
            $trialCnt = min($maxTrialCountByYear, count(GameConst::$allItems));

            foreach ($nationGen->getItems() as $item) {
              if (!$item->isBuyable()) {
                $trialCnt -= 1;
              }
            }

            if ($trialCnt <= 0) {
              continue;
            }

            $score = $nationGen->getVar('belong') + 10;

            if ($nationGen->getVar('officer_level') == 12) {
              $score += 60;
            } else if ($nationGen->getVar('officer_level') == 11) {
              $score += 30;
            } else if ($nationGen->getVar('officer_level') > 4) {
              $score += 15;
            }

            $score *= 2 ** $trialCnt;

            $uniqueLotteryWeightList[$nationGen->getID()] = [$nationGen, $score];
          }

          $nationLevelUpRNG = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
            UniqueConst::$hiddenSeed,
            'nationLevelUp',
            $year,
            $month,
            $nationID
          )));

          foreach (Util::range($levelDiff) as $idx) {
            if (!$uniqueLotteryWeightList) {
              break;
            }

            /** @var General */
            $winnerObj = $nationLevelUpRNG->choiceUsingWeightPair($uniqueLotteryWeightList);
            unset($uniqueLotteryWeightList[$winnerObj->getID()]);

            $givenUniqueRNG = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
              UniqueConst::$hiddenSeed,
              'givenUnique',
              $year,
              $month,
              $nationID,
              $winnerObj->getID(),
            )));
            giveRandomUniqueItem($givenUniqueRNG, $winnerObj, '작위보상');
            $winnerObj->applyDB($db);
          }

          if ($chiefID) {
            $chiefObj = General::createGeneralObjFromDB($chiefID, ['belong', 'npc', 'aux'], 2);
            $chiefObj->increaseInheritancePoint(InheritanceKey::unifier, 250 * $levelDiff);
            $chiefObj->applyDB($db);
          }
        }
      }
    }
  }
}
