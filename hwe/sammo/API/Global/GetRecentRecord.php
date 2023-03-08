<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\DB;
use sammo\KVStorage;
use sammo\Util;
use sammo\Validator;

class GetRecentRecord extends \sammo\BaseAPI
{
  static bool $allowExternalAPI = false;

  const ROW_LIMIT = 15;

  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('integer', 'lastGeneralRecordID')
      ->rule('integer', 'lastWorldHistoryID');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    $this->args['lastGeneralRecordID'] = (int)($this->args['lastGeneralRecordID'] ?? 0);
    $this->args['lastWorldHistoryID'] = (int)($this->args['lastWorldHistoryID'] ?? 0);
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN | static::REQ_READ_ONLY;
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

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $db = DB::db();

    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheValues(['isunited', 'opentime', 'refresh']);

    $lastHistoryID = $this->args['lastWorldHistoryID'];
    $lastRecordID = $this->args['lastGeneralRecordID'];

    $history = $this->getHistory($lastHistoryID);
    $globalRecord = $this->getGlobalRecord($lastRecordID);
    $generalRecord = $this->getGeneralRecord($session->generalID, $lastRecordID);

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
      'result' => true,
      'history' => $history,
      'global' => $globalRecord,
      'general' => $generalRecord,
      'flushHistory' => $flushHistory ? 1 : 0,
      'flushGlobal' => $flushGlobalRecord ? 1 : 0,
      'flushGeneral' => $flushGeneralRecord ? 1 : 0,
    ];
  }
}
