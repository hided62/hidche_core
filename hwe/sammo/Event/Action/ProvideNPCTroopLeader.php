<?php

namespace sammo\Event\Action;

use sammo\DB;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\_setGeneralCommand;
use function sammo\buildGeneralCommandClass;

class ProvideNPCTroopLeader extends \sammo\Event\Action
{
  const MaxNPCTroopLeaderCnt = [
    1 => 0,
    2 => 1,
    3 => 3,
    4 => 4,
    5 => 6,
    6 => 7,
    7 => 9
  ];

  public function run(array $env)
  {
    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $NPCTroopLeaderCntByNation = [];
    foreach ($db->queryAllLists('SELECT nation,count(no) FROM general WHERE npc = 5 GROUP BY nation') as [$nationID, $NPCTroopLeaderCnt]) {
      $NPCTroopLeaderCntByNation[$nationID] = $NPCTroopLeaderCnt;
    };

    $year = $env['year'];
    $month = $env['month'];

    foreach ($db->query('SELECT nation,name,level,tech,aux FROM nation') as $nation) {
      $nationID = $nation['nation'];
      $maxNPCTroopLeaderCnt = self::MaxNPCTroopLeaderCnt[$nation['level']] ?? 0;
      $NPCTroopLeaderCnt = $NPCTroopLeaderCntByNation[$nationID] ?? 0;

      if ($NPCTroopLeaderCnt >= $maxNPCTroopLeaderCnt) {
        continue;
      }

      $lastNPCTroopLeaderID = $gameStor->lastNPCTroopLeaderID ?? 0;

      $troopLeaderRng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
        UniqueConst::$hiddenSeed,
        'troopLeader',
        $year,
        $month,
        $nationID
      )));

      while ($NPCTroopLeaderCnt < $maxNPCTroopLeaderCnt) {
        $lastNPCTroopLeaderID += 1;
        $npcObj = new \sammo\Scenario\GeneralBuilder(
          $troopLeaderRng,
          sprintf('부대장%4d', $lastNPCTroopLeaderID),
          false,
          null,
          $nation['nation']
        );
        $npcObj->setAffinity(999)->setStat(10, 10, 10)
          ->setSpecialSingle(null)->setEgo('che_은둔')
          ->setSpecYear(999, 999)
          ->setKillturn(70)->setGoldRice(0, 0)
          ->setNPCType(5)->fillRemainSpecAsZero($env);
        $npcObj->build($env);
        $npcID = $npcObj->getGeneralID();

        $db->insert('troop', [
          'troop_leader' => $npcID,
          'name' => $npcObj->getGeneralName(),
          'nation' => $nation['nation'],
        ]);
        $db->update('general', [
          'troop' => $npcID
        ], 'no=%i', $npcID);

        $cmd = buildGeneralCommandClass('che_집합', General::createObjFromDB($npcID), $env);
        _setGeneralCommand($cmd, iterator_to_array(Util::range(GameConst::$maxTurn)));
        $NPCTroopLeaderCnt += 1;
        $gameStor->lastNPCTroopLeaderID = $lastNPCTroopLeaderID;
      }
    }
  }
}
