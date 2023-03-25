<?php

namespace sammo\API\City;

use ArrayObject;
use Ds\Set;
use sammo\CityConst;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\General;
use sammo\Json;
use sammo\Session;
use sammo\Util;
use sammo\Validator;

use function sammo\calcLeadershipBonus;
use function sammo\checkLimit;
use function sammo\checkSecretPermission;
use function sammo\getAllNationStaticInfo;
use function sammo\getBillByLevel;
use function sammo\getDedLevelText;
use function sammo\getHonor;
use function sammo\getNationStaticInfo;
use function sammo\getOfficerLevelText;
use function sammo\increaseRefresh;

class GetCityInfo extends \sammo\BaseAPI
{
  const FAR_CITY = 0;
  const NEAR_CITY = 1;
  const ON_CITY = 2;
  const OUR_NATION = 3;

  static $viewColumns = [
    'no' => self::NEAR_CITY,
    'name' => self::NEAR_CITY,
    'nation' => self::NEAR_CITY,
    'npc' => self::NEAR_CITY,
    'injury' => self::NEAR_CITY,
    'leadership' => self::NEAR_CITY,
    'strength' => self::NEAR_CITY,
    'intel' => self::NEAR_CITY,

    'picture' => self::NEAR_CITY,
    'imgsvr' => self::NEAR_CITY,

    'city' => self::NEAR_CITY,

    'officer_level' => self::NEAR_CITY,
    'officer_city' => self::NEAR_CITY,
    'defence_train' => self::OUR_NATION,
    'crewtype' => self::OUR_NATION,
    'crew' => self::ON_CITY,
    'train' => self::OUR_NATION,
    'atmos' => self::OUR_NATION,
  ];

  static $columnRemap = [
    'nation' => 'nationID',
  ];

  static $customViewColumns = [
    'officerLevel' => self::NEAR_CITY,
    'officerLevelText' => self::NEAR_CITY,
    'lbonus' => self::NEAR_CITY,
    'reservedCommand' => self::OUR_NATION,
    'isOurNation' => self::NEAR_CITY,
  ];

  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('int', 'troopID');

    if (!$v->validate()) {
      return $v->errorStr();
    }
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
  }

  private function getOfficerLevel($rawGeneral)
  {
    $level = $rawGeneral['officer_level'];
    return $level;
  }

  private function filterCity(array $rawCity, int $showLevel)
  {
    $cityID = $rawCity['city'];
    $db = DB::db();

    $officerList = $db->query(
      'SELECT no, npc, name, nation, picture, imgsvr FROM general WHERE city = %i AND 2 <= officer_level AND officer_level <= 4',
      $cityID
    );

    $filteredCity = [
      'city' => $cityID,
      'name' => $rawCity['name'],
      'level' => $rawCity['level'],
      'nation' => $rawCity['nation'],
      'supply' => $rawCity['supply'],
      'pop_max' => $rawCity['pop_max'],
      'agri_max' => $rawCity['agri_max'],
      'comm_max' => $rawCity['comm_max'],
      'secu_max' => $rawCity['secu_max'],
      'trade' => $rawCity['trade'],
      'def_max' => $rawCity['def_max'],
      'wall_max' => $rawCity['wall_max'],
      'region' => $rawCity['region'],
      'officerList' => $officerList,
    ];

    if ($showLevel >= self::ON_CITY) {
      $filteredCity = array_merge($filteredCity, [
        'pop' => $rawCity['pop'],
        'agri' => $rawCity['agri'],
        'comm' => $rawCity['comm'],
        'secu' => $rawCity['secu'],
        'def' => $rawCity['def'],
        'wall' => $rawCity['wall'],
        'officer_set' => $rawCity['officer_set'],
      ]);
    }

    return $filteredCity;
  }

  private function getGeneralList(int $cityID, int $nationID, int $showLevel)
  {
    if ($showLevel == self::FAR_CITY) {
      return [
        'column' => [],
        'list' => []
      ];
    }
    $db = DB::db();

    $nationStaticList = getAllNationStaticInfo();

    $rawGeneralList = Util::convertArrayToDict($db->query('SELECT %l from general WHERE city = %i ORDER BY turntime ASC', Util::formatListOfBackticks(array_keys(static::$viewColumns)), $cityID), 'city');
    $ourNationGeneralIDList = new Set();
    foreach ($rawGeneralList as $rawGeneral) {
      if ($rawGeneral['nation'] == $nationID) {
        $ourNationGeneralIDList->add($rawGeneral['no']);
      }
    }

    $reservedCommand = [];
    $reservedCommandTargetGeneralIDList = [];

    foreach ($rawGeneralList as $rawGeneral) {
      if ($rawGeneral['nation'] != $nationID) {
        continue;
      }
      if ($rawGeneral['npc'] < 2) {
        $reservedCommandTargetGeneralIDList[$rawGeneral['no']] = $rawGeneral['no'];
      }
    }

    if ($reservedCommandTargetGeneralIDList) {
      $rawTurnList = $db->query(
        'SELECT general_id, turn_idx, action, arg, brief FROM general_turn WHERE general_id IN %li AND turn_idx < 5 ORDER BY general_id asc, turn_idx asc',
        array_values($reservedCommandTargetGeneralIDList)
      );

      foreach ($rawTurnList as $rawTurn) {
        [
          'general_id' => $generalID,
          'action' => $action,
          'arg' => $arg,
          'brief' => $brief,
        ] = $rawTurn;
        if (!key_exists($generalID, $reservedCommand)) {
          $reservedCommand[$generalID] = [];
        }
        $reservedCommand[$generalID][] = [
          'action' => $action,
          'arg' => $arg,
          'brief' => $brief
        ];
      }
    }

    $specialViewFilter = [
      'officerLevel' => fn ($rawGeneral) => $this->getOfficerLevel($rawGeneral),
      'officerLevelText' => fn ($rawGeneral) => getOfficerLevelText($this->getOfficerLevel($rawGeneral), $nationStaticList[$rawGeneral['nation']]['level']),
      'lbonus' => fn ($rawGeneral) => calcLeadershipBonus($rawGeneral['officer_level'], $nationStaticList[$rawGeneral['nation']]['level']),
      'reservedCommand' => fn ($rawGeneral) => $reservedCommand[$rawGeneral['no']] ?? null,
      'isOurNation' => fn ($rawGeneral) => $rawGeneral['nation'] == $nationID,
    ];

    $showFullColumn = $showLevel == self::OUR_NATION || $ourNationGeneralIDList->count() > 0;

    $resultColumns = [];
    foreach (static::$viewColumns as $column => $reqShowLevel) {
      if (!$showFullColumn && $reqShowLevel > $showLevel) {
        continue;
      }
      if (key_exists($column, static::$columnRemap)) {
        $newColumn = static::$columnRemap[$column];
        if ($newColumn !== null) {
          $resultColumns[$newColumn] = $column;
        }
      } else {
        $resultColumns[$column] = $column;
      }
    }

    foreach (static::$customViewColumns as $column => $reqShowLevel) {
      if (!$showFullColumn && $reqShowLevel > $showLevel) {
        continue;
      }
      $resultColumns[$column] = $column;
    }

    $generalList = [];
    foreach ($rawGeneralList as $rawGeneral) {
      $item = [];
      foreach ($resultColumns as $column) {
        $reqShowLevel = static::$viewColumns[$column];

        if ($rawGeneral['nation'] != $nationID && $reqShowLevel == self::OUR_NATION) {
          $item[] = null;
          continue;
        }

        if (key_exists($column, $specialViewFilter)) {
          $value = $specialViewFilter[$column]($rawGeneral);
        } else {
          $value = $rawGeneral[$column];
        }
        $item[] = $value;
      }

      $generalList[] = $item;
    }
    return [
      'column' => array_keys($resultColumns),
      'list' => $generalList,
    ];
  }

  private function calcShowLevel(int $cityID, int $currentCityID, int $nationID, Set $cityList, bool $isSpyPresent): int
  {
    $db = DB::db();
    if ($cityList->contains($currentCityID)) {
      return self::OUR_NATION;
    }
    if ($cityID == $currentCityID) {
      return self::ON_CITY;
    }
    if ($isSpyPresent) {
      return self::ON_CITY;
    }

    $existsGeneralID = $db->queryFirstField(
      'SELECT no FROM general WHERE city = %i AND nation = %i LIMIT 1',
      $cityID,
      $nationID
    );

    if ($existsGeneralID) {
      return self::ON_CITY;
    }

    $cityConstObj = CityConst::byID($cityID);

    $nearCityIDList = array_keys($cityConstObj->path);
    foreach ($nearCityIDList as $nearCityID) {
      if ($cityList->contains($nearCityID)) {
        return self::NEAR_CITY;
      }
    }

    return self::FAR_CITY;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    increaseRefresh("도시정보", 1);

    $db = DB::db();

    $gameStor = \sammo\KVStorage::getStorage($db, 'game_env');
    $lastExecuted = $gameStor->getValue('turntime');

    $me = $db->queryFirstRow('SELECT con, turntime, belong, city, nation, officer_level, permission, penalty FROM general WHERE owner=%i', $session->getUserID());

    if (key_exists('cityID', $this->args)) {
      $cityID = (int)$this->args['cityID'];
    } else {
      $cityID = $me['city'];
    }

    $con = checkLimit($me['con']);
    if ($con >= 2) {
      return '접속 제한중입니다. 1턴 이내에 너무 많은 갱신을 하셨습니다.';
    }

    $nationID = $me['nation'];
    $currentCityID = $me['city'];
    //TODO: 조건 조사

    $cityList = new Set($db->queryFirstField('SELECT city FROM city WHERE nation=%i', $nationID));

    $spyList = JSON::decode($db->queryFirstField('SELECT spy FROM nation WHERE nation=%i', $nationID));
    $showLevel = $this->calcShowLevel($cityID, $currentCityID, $nationID, $cityList, key_exists($cityID, $spyList));

    $rawCity = $db->queryFirstRow('SELECT * FROM city WHERE no=%i', $cityID);
    $filteredCity = $this->filterCity($rawCity, $showLevel);

    $result = [
      'result' => true,
      'lastExcuted' => $lastExecuted,
      'cityInfo' => $filteredCity,
      'spyList' => $spyList,
      'nationCityList' => $cityList->toArray(),
      'myGeneralID' => $session->generalID,
      'currentCityID' => $currentCityID,
      'cityGeneralList' => $this->getGeneralList($cityID, $nationID, $showLevel)
    ];

    return $result;
  }
}
