<?php

namespace sammo\API\Nation;

use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\GeneralQueryMode;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\LastTurn;
use sammo\Session;
use sammo\Util;
use sammo\Validator;

use function sammo\buildNationCommandClass;
use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getBattleDetailLogMore;
use function sammo\getBattleResultMore;
use function sammo\getBattleResultRecent;
use function sammo\getGeneralActionLogMore;
use function sammo\getGeneralActionLogRecent;
use function sammo\getGeneralHistoryLogAll;
use function sammo\getNationStaticInfo;

class GetNationInfo extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
        $v->rule('in', 'full', ['true', 'false']);

        if (!$v->validate()) {
            return $v->errorStr();
        }
        return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $userID = $session->userID;

    $db = DB::db();
    $nationID = $db->queryFirstField('SELECT nation FROM general WHERE `owner` = %i', $userID);

    if (!$nationID) {
      return [
        'result' => true,
        'nation' => getNationStaticInfo(0)
      ];
    }

    $isFull = $this->args['full'] ?? false;

    if(!$isFull){
      $nation = getNationStaticInfo($nationID);
      return [
        'result' => true,
        'nation' => $nation,
      ];
    }

    $generalObj = General::createGeneralObjFromDB($session->generalID, null, GeneralQueryMode::FullWithoutIAction);

    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameEnv = $gameStor->getValues(['year', 'month', 'startyear']);

    $nation = $db->queryFirstRow(
      'SELECT nation, `name`, color, capital, gennum, gold, rice, bill, rate, secretlimit, chief_set, scout, war, strategic_cmd_limit, surlimit, tech, `power`, `level`, `type` FROM nation WHERE nation = %i',
      $nationID
    );
    $nationStor = \sammo\KVStorage::getStorage($db, $nationID, 'nation_env');
    $nationStor->cacheAll();


    //전략 정보를 매번 읽어와야하나?
    $impossibleStrategicCommandLists = [];
    $strategicCommandLists = GameConst::$availableChiefCommand['전략'];
    $yearMonth = Util::joinYearMonth($gameEnv['year'], $gameEnv['month']);
    foreach ($strategicCommandLists as $command) {
        $cmd = buildNationCommandClass($command, $generalObj, $gameEnv, new LastTurn());
        $nextAvailableTurn = $cmd->getNextAvailableTurn();
        if ($nextAvailableTurn > $yearMonth) {
            $impossibleStrategicCommandLists[] = [$cmd->getName(), $nextAvailableTurn - $yearMonth];
        }
    }

    $troopName = [];
    foreach ($db->queryAllLists('SELECT troop_leader, name FROM troop WHERE nation=%i', $nationID) as [$troopID, $tName]) {
        $troopName[$troopID] = $tName;
    }

    $result = [
      'result' => true,
      'isFull' => $isFull,
      'nation' => $nation,
      'impossibleStrategicCommandLists' => $impossibleStrategicCommandLists,
      'troops' => $troopName,
    ];

    return $result;
  }
}
