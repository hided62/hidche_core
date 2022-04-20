<?php

namespace sammo\API\Global;

use sammo\DB;
use sammo\Json;
use sammo\KVStorage;
use sammo\Session;

use function sammo\getAllNationStaticInfo;
use function sammo\increaseRefresh;

class GetDiplomacy extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
  {

    $userID = $session->userID;
    increaseRefresh("중원정보", 1);

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');

    $myNationID = $db->queryFirstField('SELECT nation FROM general WHERE owner=%i', $userID);


    $nations = array_filter(getAllNationStaticInfo(), function (array $nation) {
      return $nation['level'];
    });
    uasort($nations, function (array $lhs, array $rhs) {
      return - ($lhs['power'] <=> $rhs['power']);
    });
    foreach(array_keys($nations) as $nationID){
      $nations[$nationID]['cities'] = [];
    }

    $realConflict = [];
    foreach ($db->queryAllLists('SELECT nation, city, name, conflict FROM city') as [
      $nationID,
      $cityID,
      $cityName,
      $rawConflict
    ]) {
      if($nationID != 0){
        $nations[$nationID]['cities'][] = $cityName;
      }

      if($rawConflict == '{}'){
        continue;
      }
      $rawConflict = Json::decode($rawConflict);
      if (count($rawConflict) < 2) {
        continue;
      }

      $sum = array_sum($rawConflict);

      $conflict = [];
      foreach ($rawConflict as $nationID => $killnum) {
        $conflict[$nationID] = [
          'killnum' => $killnum,
          'percent' => round(100 * $killnum / $sum, 1),
        ];
      }

      $realConflict[] = [$cityID, $conflict];
    };

    $neutralDiplomacyMap = [
      3 => 2,
      4 => 2,
      5 => 2,
      6 => 2,
      7 => 2,
    ];

    $diplomacyList = [];
    foreach ($db->queryAllLists('SELECT me, you, state FROM diplomacy') as [$me, $you, $state]) {
      if (!key_exists($me, $diplomacyList)) {
        $diplomacyList[$me] = [];
      }

      if ($me != $myNationID && $you != $myNationID) {
        $diplomacyList[$me][$you] = $neutralDiplomacyMap[$state] ?? $state;
      } else {
        $diplomacyList[$me][$you] = $state;
      }
    }

    return [
      'result' => true,
      'nations' => array_values($nations),
      'conflict' => $realConflict,
      'diplomacyList' => $diplomacyList,
      'myNationID' => $myNationID,
    ];
  }
}
