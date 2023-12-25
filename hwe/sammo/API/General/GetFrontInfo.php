<?php

namespace sammo\API\General;

use DateTimeInterface;
use MeekroDB;
use sammo\CityConst;
use sammo\Command\UserActionCommand;
use sammo\DB;
use sammo\DTO\UserAction;
use sammo\DTO\VoteInfo;
use sammo\Enums\APIRecoveryType;
use sammo\Enums\CityColumn;
use sammo\Enums\GeneralAccessLogColumn;
use sammo\Enums\GeneralAuxKey;
use sammo\Enums\GeneralColumn;
use sammo\Enums\GeneralQueryMode;
use sammo\Enums\RankColumn;
use sammo\GameConst;
use sammo\General;
use sammo\KVStorage;
use sammo\LastTurn;
use sammo\Validator;

use sammo\Session;
use sammo\TimeUtil;
use sammo\Util;

use function sammo\buildNationCommandClass;
use function sammo\checkLimit;
use function sammo\getTournamentTermText;
use function sammo\buildNationTypeClass;
use function sammo\buildUserActionCommandClass;
use function sammo\calcLeadershipBonus;
use function sammo\checkSecretPermission;
use function sammo\getBillByLevel;
use function sammo\getDed;
use function sammo\getHonor;
use function sammo\getNationStaticInfo;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

/**
 * 통째로 메인페이지의 주요 정보를 반환하는 날로먹는 API
 */
class GetFrontInfo extends \sammo\BaseAPI
{
  const ROW_LIMIT = 15;

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('lengthMin', 'lastNationNoticeDate', 19)
      ->rule('integer', 'lastGeneralRecordID')
      ->rule('integer', 'lastWorldHistoryID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['lastGeneralRecordID'] = (int)($this->args['lastGeneralRecordID'] ?? 0);
    $this->args['lastWorldHistoryID'] = (int)($this->args['lastWorldHistoryID'] ?? 0);
    return null;
  }

  private function getGlobalRecord(int $lastRecordID): array
  {
    $db = DB::db();

    $globalRecord = $db->queryAllLists(
      'SELECT id, `text` FROM general_record WHERE `general_id` = 0 AND log_type = %s AND id >= %i ORDER BY `id` DESC LIMIT %i',
      'history',
      $lastRecordID,
      static::ROW_LIMIT + 1,
    );
    return $globalRecord;
  }

  private function getGeneralRecord(int $generalID, int $lastRecordID): array
  {
    $db = DB::db();

    $generalRecord = $db->queryAllLists(
      'SELECT id, `text` FROM general_record WHERE `general_id` = %i AND log_type = %s AND id >= %i ORDER BY `id` DESC LIMIT %i',
      $generalID,
      'action',
      $lastRecordID,
      static::ROW_LIMIT + 1,
    );
    return $generalRecord;
  }

  private function getHistory(int $lastHistoryID): array
  {
    $db = DB::db();

    $history = $db->queryAllLists(
      'SELECT id, `text` FROM world_history WHERE nation_id = 0 AND id >= %i ORDER BY `id` DESC LIMIT %i',
      $lastHistoryID,
      static::ROW_LIMIT + 1,
    );
    return $history;
  }

  private function generateRecentRecord(int $generalID)
  {
    $db = DB::db();

    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheValues(['isunited', 'opentime', 'refresh']);

    $lastHistoryID = $this->args['lastWorldHistoryID'];
    $lastRecordID = $this->args['lastGeneralRecordID'];

    $history = $this->getHistory($lastHistoryID);
    $globalRecord = $this->getGlobalRecord($lastRecordID);
    $generalRecord = $this->getGeneralRecord($generalID, $lastRecordID);

    $flushHistory = false;
    $flushGlobalRecord = false;
    $flushGeneralRecord = false;

    if (!$history) {
      $flushHistory = false;
    } else if (Util::array_last($history)[0] <= $lastHistoryID) {
      $flushHistory = false;
      array_pop($history);
    } else if (count($history) > static::ROW_LIMIT) {
      array_pop($history);
    }

    if (!$globalRecord) {
      $flushGlobalRecord = false;
    } else if (Util::array_last($globalRecord)[0] == $lastRecordID) {
      $flushGlobalRecord = false;
      array_pop($globalRecord);
    } else if (count($globalRecord) > static::ROW_LIMIT) {
      array_pop($globalRecord);
    }

    if (!$generalRecord) {
      $flushGeneralRecord = false;
    } else if (Util::array_last($generalRecord)[0] == $lastRecordID) {
      $flushGeneralRecord = false;
      array_pop($generalRecord);
    } else if (count($generalRecord) > static::ROW_LIMIT) {
      array_pop($generalRecord);
    }

    return [
      'history' => $history,
      'global' => $globalRecord,
      'general' => $generalRecord,
      'flushHistory' => $flushHistory ? 1 : 0,
      'flushGlobal' => $flushGlobalRecord ? 1 : 0,
      'flushGeneral' => $flushGeneralRecord ? 1 : 0,
    ];
  }

  private function generateGlobalInfo(MeekroDB $db): array
  {
    $gameStor = KVStorage::getStorage($db, 'game_env');

    [
      $scenarioText, $extendedGeneral, $isFiction, $npcMode,
      $joinMode, $autorunUser, $turnterm, $lastExecuted,
      $lastVoteID, $develCost, $noticeMsg,
      $onlineNations, $onlineUserCnt,
      $year, $month, $startYear,
      $generalCntLimit,
      $apiLimit,
      $serverCnt,
    ] = $gameStor->getValuesAsArray([
      'scenario_text', 'extended_general', 'fiction', 'npcmode',
      'join_mode', 'autorun_user', 'turnterm', 'turntime',
      'lastVote', 'develcost', 'msg',
      'online_nation', 'online_user_cnt',
      'year', 'month', 'startyear',
      'maxgeneral',
      'refreshLimit',
      'server_cnt',
    ]);

    $lastVote = null;
    if ($lastVoteID) {
      $voteStor = KVStorage::getStorage($db, 'vote');
      $lastVote = VoteInfo::fromArray($voteStor->getValue("vote_{$lastVoteID}"));
      if ($lastVote->endDate && $lastVote->endDate < TimeUtil::now()) {
        $lastVote = null;
      }
    }

    $auctionCount = $db->queryFirstField('SELECT count(*) FROM ng_auction WHERE finished = 0');
    $isTournamentActive = $gameStor->tournament > 0;
    $isTournamentApplicationOpen = $gameStor->tournament == 1;
    $isBettingActive = $gameStor->tournament == 6;
    $tournamentType = $gameStor->tnmt_type;
    $tournamentState = $gameStor->tournament;
    $tournamentTime = $gameStor->tnmt_time;
    $isLocked = boolval($db->queryFirstField('SELECT plock FROM plock WHERE `type`="GAME" LIMIT 1'));

    $globalGenCount = $db->queryAllLists('SELECT npc, count(no) FROM general GROUP BY npc');

    return [
      'scenarioText' => $scenarioText,
      'extendedGeneral' => $extendedGeneral,
      'isFiction' => $isFiction,
      'npcMode' => $npcMode,
      'joinMode' => $joinMode,
      'startyear' => $startYear,
      'year' => $year,
      'month' => $month,
      'autorunUser' => $autorunUser,
      'turnterm' => $turnterm,
      'lastExecuted' => $lastExecuted,
      'lastVoteID' => $lastVoteID,
      'develCost' => $develCost,
      'noticeMsg' => $noticeMsg,
      'onlineNations' => $onlineNations,
      'onlineUserCnt' => $onlineUserCnt,
      'apiLimit' => $apiLimit,
      'auctionCount' => $auctionCount,
      'isTournamentActive' => $isTournamentActive,
      'isTournamentApplicationOpen' => $isTournamentApplicationOpen,
      'isBettingActive' => $isBettingActive,
      'isLocked' => $isLocked,
      'tournamentType' => $tournamentType,
      'tournamentState' => $tournamentState,
      'tournamentTime' => $tournamentTime,
      'genCount' => $globalGenCount,
      'generalCntLimit' => $generalCntLimit,
      'serverCnt' => $serverCnt,
      'lastVote' => $lastVote === null ? null : $lastVote->toArray(),
    ];
  }

  public function generateNationInfo(MeekroDB $db, General $general, array $rawNation): array
  {
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getAll(true);

    $lastNationNoticeDate = $this->args['lastNationNoticeDate'] ?? '2022-08-19 00:00:00';

    $nationID = $general->getNationID();

    //XXX: 매번 더하는가?
    $nationPopulation = $db->queryFirstRow(
      'SELECT COUNT(*) as cityCnt, CAST(SUM(pop) AS INTEGER) as `now`, CAST(SUM(pop_max) AS INTEGER) as `max` from city where nation=%i',
      $nationID
    );

    if ($nationPopulation['cityCnt'] == 0) {
      $nationPopulation = [
        'cityCnt' => 0,
        'now' => 0,
        'max' => 0
      ];
    }

    //XXX: 매번 더하는가?
    $nationCrew = $db->queryFirstRow(
      'SELECT COUNT(*) as generalCnt, CAST(SUM(crew) AS INTEGER) as `now`,CAST(SUM(leadership)*100 AS INTEGER) as `max` from general where nation=%i AND npc != 5',
      $nationID
    );

    $nationStor = KVStorage::getStorage($db, $nationID, 'nation_env');
    [$nationNotice, $onlineGen] = $nationStor->getValuesAsArray(['nationNotice', 'online_genenerals']);
    if (!$nationNotice || ($nationNotice['date'] ?? '') <= $lastNationNoticeDate) {
      $nationNotice = null;
    }

    $topChiefs = Util::convertArrayToDict($db->query(
      'SELECT officer_level, no, name, npc FROM general WHERE nation = %i AND officer_level >= 11',
      $nationID
    ), 'officer_level');

    $impossibleStrategicCommandLists = [];
    $strategicCommandLists = GameConst::$availableChiefCommand['전략'];
    $yearMonth = Util::joinYearMonth($admin['year'], $admin['month']);
    foreach ($strategicCommandLists as $command) {
      $cmd = buildNationCommandClass($command, $general, $admin, new LastTurn());
      $nextAvailableTurn = $cmd->getNextAvailableTurn();
      if ($nextAvailableTurn > $yearMonth) {
        $impossibleStrategicCommandLists[] = [$cmd->getName(), $nextAvailableTurn - $yearMonth];
      }
    }

    $nationClass = buildNationTypeClass($rawNation['type']);

    return [
      'id' => $nationID,
      'full' => true,
      'name' => $rawNation['name'],
      'population' => $nationPopulation,
      'crew' => $nationCrew,
      'type' => [
        'raw' => $rawNation['type'],
        'name' => $nationClass->getName(),
        'pros' => $nationClass::$pros,
        'cons' => $nationClass::$cons,
      ],
      'color' => $rawNation['color'],
      'level' => $rawNation['level'],
      'capital' => $rawNation['capital'],
      'gold' => $rawNation['gold'],
      'rice' => $rawNation['rice'],
      'tech' => $rawNation['tech'],
      'gennum' => $rawNation['gennum'],
      'power' => $rawNation['power'],

      'bill' => $rawNation['bill'],
      'taxRate' => $rawNation['rate'],
      'onlineGen' => $onlineGen,
      'notice' => $nationNotice,
      'topChiefs' => $topChiefs,

      'diplomaticLimit' => $rawNation['surlimit'],
      'strategicCmdLimit' => $rawNation['strategic_cmd_limit'],
      'impossibleStrategicCommand' => $impossibleStrategicCommandLists,

      'prohibitScout' => $rawNation['scout'],
      'prohibitWar' => $rawNation['war'],
    ];
  }

  public function generateDummyNationInfo(MeekroDB $db, General $general, array $rawNation): array
  {
    $staticInfo = getNationStaticInfo(0);
    return [
      'id' => 0,
      'full' => false,
      'name' => $staticInfo['name'],
      'population' => [
        'cityCnt' => 0,
        'now' => 0,
        'max' => 0,
      ],
      'crew' => [
        'generalCnt' => 0,
        'now' => 0,
        'max' => 0,
      ],
      'type' => [
        'raw' => 'None',
        'name' => '-',
        'pros' => '',
        'cons' => '',
      ],
      'color' => $staticInfo['color'],
      'level' => $staticInfo['level'],
      'capital' => $staticInfo['capital'],
      'gold' => $staticInfo['gold'],
      'rice' => $staticInfo['rice'],
      'tech' => $staticInfo['tech'],
      'gennum' => $staticInfo['gennum'],
      'power' => $staticInfo['power'],
      'onlineGen' => '', //TODO 접속자
      'notice' => '',
      'topChiefs' => [],
      'impossibleStrategicCommand' => [],
    ];
  }

  public function generateGeneralInfo(MeekroDB $db, General $general, array $rawNation): array
  {
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $admin = $gameStor->getAll(true);

    $permission = checkSecretPermission($general->getRaw());

    $result = [
      'no' => $general->getID(), // number;
      'name' => $general->getName(), // string;
      'nation' => $general->getNationID(), // number;
      'npc' => $general->getNPCType(), // number;
      'injury' => $general->getVar(GeneralColumn::injury), // number;
      'leadership' => $general->getVar(GeneralColumn::leadership), // number;
      'strength' => $general->getVar('strength'), // number;
      'intel' => $general->getVar('intel'), // number;
      'explevel' => $general->getVar('explevel'), // number;
      'dedlevel' => $general->getVar('dedlevel'), // number;
      'gold' => $general->getVar('gold'), // number;
      'rice' => $general->getVar('rice'), // number;
      'killturn' => $general->getVar(GeneralColumn::killturn), // number;
      'picture' => $general->getVar(GeneralColumn::picture), // string;
      'imgsvr' => $general->getVar(GeneralColumn::imgsvr), // 0 | 1;
      'age' => $general->getVar(GeneralColumn::age), // number;
      'specialDomestic' => $general->getVar(GeneralColumn::special), // GameObjClassKey;
      'specialWar' => $general->getVar(GeneralColumn::special2), // GameObjClassKey;
      'personal' => $general->getVar(GeneralColumn::personal), // GameObjClassKey;
      'belong' => $general->getVar(GeneralColumn::belong), // number;

      'refreshScoreTotal' => $general->getAccessLogVar(GeneralAccessLogColumn::refreshScoreTotal, 0), // number;

      'officerLevel' => $general->getVar(GeneralColumn::officer_level), // number;
      'officerLevelText' => getOfficerLevelText($general->getVar(GeneralColumn::officer_level), $rawNation['level']), // string;
      'lbonus' => calcLeadershipBonus($general->getVar(GeneralColumn::officer_level), $rawNation['level']), // number;
      'ownerName' => $general->getVar(GeneralColumn::owner_name), // string | null;
      'honorText' => getHonor($general->getVar(GeneralColumn::experience)), // string;
      'dedLevelText' => getDed($general->getVar(GeneralColumn::dedication)), // string;
      'bill' => getBillByLevel($general->getVar(GeneralColumn::dedlevel)), // number;
      'reservedCommand' => null, // TurnObj[] | null;

      'autorun_limit' => $general->getAuxVar('autorun_limit') ?? 0, // number;

      'city' => $general->getVar(GeneralColumn::city), // number;
      'troop' => $general->getVar(GeneralColumn::troop), // number;
      //P0 End

      'refreshScore' => $general->getAccessLogVar(GeneralAccessLogColumn::refreshScore, 0), // number;
      'specage' => $general->getVar(GeneralColumn::specage), // number;
      'specage2' => $general->getVar(GeneralColumn::specage2), // number;
      'leadership_exp' => $general->getVar(GeneralColumn::leadership_exp), // number;
      'strength_exp' => $general->getVar(GeneralColumn::strength_exp), // number;
      'intel_exp' => $general->getVar(GeneralColumn::intel_exp), // number;

      'dex1' => $general->getVar(GeneralColumn::dex1), // number;
      'dex2' => $general->getVar(GeneralColumn::dex2), // number;
      'dex3' => $general->getVar(GeneralColumn::dex3), // number;
      'dex4' => $general->getVar(GeneralColumn::dex4), // number;
      'dex5' => $general->getVar(GeneralColumn::dex5), // number;

      'experience' => $general->getVar(GeneralColumn::experience), // number;
      'dedication' => $general->getVar(GeneralColumn::dedication), // number;
      'officer_level' => $general->getVar(GeneralColumn::officer_level), // number;
      'officer_city' => $general->getVar(GeneralColumn::officer_city), // number;
      'defence_train' => $general->getVar(GeneralColumn::defence_train), // number;
      'crewtype' => $general->getVar(GeneralColumn::crewtype), // GameObjClassKey;
      'crew' => $general->getVar(GeneralColumn::crew), // number;
      'train' => $general->getVar(GeneralColumn::train), // number;
      'atmos' => $general->getVar(GeneralColumn::atmos), // number;
      'turntime' => $general->getVar(GeneralColumn::turntime), // string;
      'recent_war' => $general->getVar(GeneralColumn::recent_war), // string;
      'horse' => $general->getVar(GeneralColumn::horse), // GameObjClassKey;
      'weapon' => $general->getVar(GeneralColumn::weapon), // GameObjClassKey;
      'book' => $general->getVar(GeneralColumn::book), // GameObjClassKey;
      'item' => $general->getVar(GeneralColumn::item), // GameObjClassKey;

      'warnum' => $general->getRankVar(RankColumn::warnum), // number;
      'killnum' => $general->getRankVar(RankColumn::killnum), // number;
      'deathnum' => $general->getRankVar(RankColumn::deathnum), // number;
      'killcrew' => $general->getRankVar(RankColumn::killcrew), // number;
      'deathcrew' => $general->getRankVar(RankColumn::deathcrew), // number;
      'firenum' => $general->getRankVar(RankColumn::firenum), // number;
      //P1 End

      'permission' => $permission,
    ];

    $rawUserAction = $general->getAuxVar(UserActionCommand::USER_ACTION_KEY) ?? [];
    $userAction = UserAction::fromArray($rawUserAction);
    $impossibleUserAction = [];
    $yearMonth = Util::joinYearMonth($admin['year'], $admin['month']);

    if ($userAction->nextAvailableTurn) {
      foreach ($userAction->nextAvailableTurn as $command => $nextAvailableTurn) {
        if ($nextAvailableTurn > $yearMonth) {
          $impossibleUserAction[] = [$command, $nextAvailableTurn - $yearMonth];
        }
      }
    }

    $result['impossibleUserAction'] = $impossibleUserAction;

    $nationID = $general->getNationID();
    $troopID = $general->getVar(GeneralColumn::troop);
    if (!$troopID) {
      return $result;
    }

    $troopName = $db->queryFirstField(
      'SELECT `name` FROM troop WHERE nation = %i AND troop_leader = %i',
      $nationID,
      $troopID
    );
    if (!$troopName) {
      return $result;
    }

    $troopCityID = $db->queryFirstField(
      'SELECT city FROM general WHERE nation = %i AND `no` = %i',
      $nationID,
      $troopID
    );
    if (!$troopCityID) {
      return $result;
    }

    $troopReservedCommand = $db->query(
      'SELECT action, arg, brief FROM general_turn WHERE general_id = %i AND turn_idx < 5 ORDER BY turn_idx asc',
      $troopID
    );
    if (!$troopReservedCommand) {
      return $result;
    }

    $result['troopInfo'] = [
      'leader' => [
        'city' => $troopCityID,
        'reservedCommand' => $troopReservedCommand,
      ],
      'name' => $troopName,
    ];


    return $result;
  }

  public function generateCityInfo(MeekroDB $db, General $general, array $rawNation): array
  {
    $rawCity = $general->getRawCity() ?? [];
    $cityID = $rawCity[CityColumn::city->value];

    $nationID = $rawCity[CityColumn::nation->value];
    if ($nationID == $general->getNationID()) {
      $nationName = $rawNation['name'];
      $nationColor = $rawNation['color'];
    } else {
      $staticNation = getNationStaticInfo($nationID);
      $nationName = $staticNation['name'];
      $nationColor = $staticNation['color'];
    }

    $rawOfficerList = $db->query('SELECT officer_level, name, npc FROM general WHERE officer_city = %i AND officer_level IN (4,3,2)', $cityID);
    $officerList = [4 => null, 3 => null, 2 => null];
    foreach ($rawOfficerList as $officer) {
      $officerLevel = $officer['officer_level'];
      $officerList[$officerLevel] = $officer;
    }

    $result = [
      'id' => $rawCity[CityColumn::city->value],
      'name' => $rawCity[CityColumn::name->value],
      'nationInfo' => [
        'id' => $nationID,
        'name' => $nationName,
        'color' => $nationColor,
      ],
      'level' => $rawCity[CityColumn::level->value],
      'trust' => $rawCity[CityColumn::trust->value],
      'pop' => [$rawCity[CityColumn::pop->value], $rawCity[CityColumn::pop_max->value]],
      'agri' => [$rawCity[CityColumn::agri->value], $rawCity[CityColumn::agri_max->value]],
      'comm' => [$rawCity[CityColumn::comm->value], $rawCity[CityColumn::comm_max->value]],
      'secu' => [$rawCity[CityColumn::secu->value], $rawCity[CityColumn::secu_max->value]],
      'def' => [$rawCity[CityColumn::def->value], $rawCity[CityColumn::def_max->value]],
      'wall' => [$rawCity[CityColumn::wall->value], $rawCity[CityColumn::wall_max->value]],
      'trade' => $rawCity[CityColumn::trade->value],
      'officerList' => $officerList,
    ];
    return $result;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $generalID = $session->generalID;
    //NOTE: 이 경우 staticNation 정보를 조회한다.
    $general = General::createObjFromDB($generalID, null, GeneralQueryMode::FullWithAccessLog);
    $nationID = $general->getNationID();
    $cityID = $general->getCityID();

    $limitState = checkLimit($general->getAccessLogVar(GeneralAccessLogColumn::refreshScore, 0));
    if ($limitState >= 2) {
      return [
        'result' => false,
        'reason' => '접속 제한중입니다.',
        'recovery' => APIRecoveryType::GameQuota,
        'recovery_arg' => $general->getVar('turntime'),
      ];
    }

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheAll(true);

    increaseRefresh("General/GetFrontInfo", 1);

    $rawCity = $db->queryFirstRow('SELECT * FROM city WHERE city = %i', $cityID);
    $general->setRawCity($rawCity);

    if ($nationID == 0) {
      $rawNation = getNationStaticInfo(0);
    } else {
      $rawNation = $db->queryFirstRow('SELECT * FROM nation WHERE nation = %i', $nationID);
    }

    $globalInfo = $this->generateGlobalInfo($db);
    $nationInfo = $nationID != 0 ?
      $this->generateNationInfo($db, $general, $rawNation) :
      $this->generateDummyNationInfo($db, $general, $rawNation);
    $generalInfo = $this->generateGeneralInfo($db, $general, $rawNation);
    $cityInfo = $this->generateCityInfo($db, $general, $rawNation);

    //TODO: 마지막 투표, 토너먼트, 베팅을 했는지 정보를 별도로 가져와야 함. aux?

    $auxInfo = [];

    if ($globalInfo['lastVote']) {
      $myLastVoteID = $db->queryFirstField('SELECT vote_id FROM vote WHERE general_id = %i ORDER BY vote_id DESC LIMIT 1', $generalID);
      if ($myLastVoteID) {
        $auxInfo['myLastVote'] = $myLastVoteID;
      }
    }

    return [
      'result' => true,
      'recentRecord' => $this->generateRecentRecord($generalID),
      'global' => $globalInfo,
      'nation' => $nationInfo,
      'general' => $generalInfo,
      'city' => $cityInfo,
      'aux' => $auxInfo,
    ];
  }
}
