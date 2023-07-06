<?php

namespace sammo\API\Global;

use sammo\Session;
use DateTimeInterface;
use sammo\APICacheResult;
use sammo\DB;
use sammo\Enums\APIRecoveryType;
use sammo\Json;
use sammo\UniqueConst;
use sammo\Util;
use sammo\Validator;

use function sammo\checkLimit;
use function sammo\increaseRefresh;
use function sammo\templateLimitMsg;

class GetHistory extends \sammo\BaseAPI
{

  public string|null $cacheHash = null;

  public function validateArgs(): ?string
  {
    $v = new Validator($this->args);
    $v->rule('required', ['year', 'month'])
      ->rule('lengthMin', 'serverID', 1)
      ->rule('integer', 'year')
      ->rule('integer', 'month');
    if (!$v->validate()) {
      return $v->errorStr();
    }
    if (!$this->args['serverID']) {
      $this->args['serverID'] = UniqueConst::$serverID;
    }
    $this->args['year'] = (int)$this->args['year'];
    $this->args['month'] = (int)$this->args['month'];
    return null;
  }

  public function getRequiredSessionMode(): int
  {
    return static::REQ_LOGIN | static::REQ_READ_ONLY;
  }

  public function tryCache(): ?APICacheResult
  {
    if (!$this->cacheHash) {
      return null;
    }
    return new APICacheResult(
      null,
      $this->getCacheKey($this->args['serverID'], $this->args['year'], $this->args['month'], $this->cacheHash)
    );
  }

  private function getHistory(string $serverID, int $year, int $month): array
  {
    $db = DB::db();
    $history = $db->queryFirstRow(
      'SELECT * FROM ng_history WHERE server_id = %s AND year = %i AND month = %i',
      $serverID,
      $year,
      $month
    );
    $hash = hash(
      'sha256',
      "[" . join(',', [
        $history['global_history'],
        $history['global_action'],
        $history['nations'],
        $history['map'],
      ]) . "]"
    );
    $history['global_history'] = Json::decode($history['global_history']);
    $history['global_action'] = Json::decode($history['global_action']);
    $history['nations'] = Json::decode($history['nations']);
    $history['map'] = Json::decode($history['map']);
    $history['hash'] = $hash;
    return $history;
  }

  public function parseEtag(?string $etag): ?array
  {
    if (!$etag) {
      return null;
    }
    $tags = explode('-', $etag);
    if (count($tags) != 4) {
      return null;
    }
    if (!is_numeric($tags[1])) {
      return null;
    }
    if (!is_numeric($tags[2])) {
      return null;
    }
    return [$tags[0], Util::toInt($tags[1]), Util::toInt($tags[2]), $tags[3]];
  }

  public function getCacheKey(string $serverID, int $year, int $month, string $cacheHash): string
  {
    return "{$serverID}-{$year}-{$month}-{$cacheHash}";
  }

  public function checkCached(?string $reqEtag): ?string
  {
    if (!$reqEtag) {
      return null;
    }
    $parsedTags = $this->parseEtag($reqEtag);

    if (!$parsedTags) {
      return null;
    }

    $serverID = $this->args['serverID'];
    $year = Util::toInt($this->args['year']);
    $month = Util::toInt($this->args['month']);

    [$cachedServerID, $cachedYear, $cachedMonth, $cachedHash] = $parsedTags;
    if ($cachedServerID !== $serverID || $cachedYear !== $year || $cachedMonth !== $month) {
      return null;
    }

    //tag로 hash를 굳이 붙이고 왔다면, 이미 데이터를 가지고 있다고 가정한다.
    return $cachedHash;
  }

  public function launch(Session $session, ?DateTimeInterface $modifiedSince, ?string $reqEtag): null | string | array | APIRecoveryType
  {
    $checkCache = $this->checkCached($reqEtag);
    if ($checkCache) {
      $this->cacheHash = $checkCache;
      return null;
    }

    $serverID = $this->args['serverID'];
    $year = $this->args['year'];
    $month = $this->args['month'];

    $db = DB::db();

    if ($serverID === UniqueConst::$serverID) {
      if (!$session->isGameLoggedIn()) {
        return '진행중인 서버의 연감은 게임에 로그인해야 볼 수 있습니다.';
      }
      increaseRefresh("연감", 1);

      $me = $db->queryFirstRow(
        'SELECT refresh_score, turntime FROM `general`
        LEFT JOIN general_access_log AS l ON `general`.no = l.general_id WHERE owner = %i', $session->userID
      );
      if (!$me) {
        return '장수가 사망했습니다.';
      }

      $limitState = checkLimit($me['refresh_score']);
      if ($limitState >= 2) {
        return templateLimitMsg($me['turntime']);
      }
    }

    [$f_year, $f_month] = $db->queryFirstList(
      'SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year ASC, month ASC LIMIT 1',
      $serverID
    );
    if ($f_year === null || $f_month === null) {
      return '올바르지 않은 서버 아이디입니다.';
    }
    $firstYearMonth = Util::joinYearMonth($f_year, $f_month);


    [$l_year, $l_month] = $db->queryFirstList(
      'SELECT year, month FROM ng_history WHERE server_id = %s ORDER BY year DESC, month DESC LIMIT 1',
      $serverID
    );
    $lastYearMonth = Util::joinYearMonth($l_year, $l_month);

    $queryYearMonth = Util::joinYearMonth($year, $month);
    if ($queryYearMonth < $firstYearMonth || $queryYearMonth > $lastYearMonth) {
      return '올바르지 않은 범위입니다.';
    }

    $history = $this->getHistory($serverID, $year, $month);
    $this->cacheHash = $history['hash'];

    return [
      'result' => true,
      'data' => $history,
    ];
  }
}
