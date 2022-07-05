<?php

namespace sammo\API\General;

use sammo\Command\General\che_거병;
use sammo\DB;
use sammo\Validator;

use sammo\Session;
use sammo\GameConst;
use sammo\General;
use sammo\JosaUtil;
use sammo\KVStorage;
use sammo\LiteHashDRBG;
use sammo\RandUtil;
use sammo\UniqueConst;
use sammo\Util;

use function sammo\increaseRefresh;

class BuildNationCandidate extends \sammo\BaseAPI
{
  public function validateArgs(): ?string
  {
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_GAME_LOGIN;
  }

  public function launch(Session $session, ?\DateTimeInterface $modifiedSince, ?string $reqEtag)
  {
    $userID = $session->userID;

    $db = DB::db();
    $gameStor = KVStorage::getStorage($db, 'game_env');
    $gameStor->cacheValues(['opentime', 'turntime']);

    $general = $db->queryFirstRow('SELECT no,name,nation,owner_name,npc,lastrefresh FROM general WHERE owner=%i', $userID);

    if (!$general) {
      return '장수가 없습니다';
    }

    $generalID = $general['no'];

    increaseRefresh("사전 거병", 1);

    if ($gameStor->turntime > $gameStor->opentime) {
      return '게임이 시작되었습니다.';
    }

    if ($general['nation'] != 0) {
      return '이미 국가에 소속되어있습니다.';
    }

    $env = $gameStor->getAll();

    $generalObj = General::createGeneralObjFromDB($general['no']);

    $cmd = new che_거병($generalObj, $env);
    $failReason = $cmd->testFullConditionMet();
    if ($failReason !== null) {
      return $failReason;
    }

    $rng = new RandUtil(new LiteHashDRBG(Util::simpleSerialize(
      UniqueConst::$hiddenSeed,
      'BuildNationCandidate',
      $generalID,
    )));
    $result = $cmd->run($rng);

    if(!$result){
      return '거병을 실패했습니다.';
    }

    return null;
  }
}
